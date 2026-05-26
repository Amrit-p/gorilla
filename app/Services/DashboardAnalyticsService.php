<?php

namespace App\Services;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadStatus;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Helpers\OptimizationHelper;
use App\Support\CrmRoles;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function dashboardTypeFor(User $user): string
    {
        if ($user->hasRole(CrmRoles::OFFICE_MANAGER)) {
            return 'admin';
        }

        if ($user->hasRole(CrmRoles::SALES_MANAGER)) {
            return 'sales';
        }

        if ($user->hasRole(CrmRoles::MOWER)) {
            return 'mower';
        }

        return 'admin';
    }

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return match ($this->dashboardTypeFor($user)) {
            'sales' => $this->sales(),
            'mower' => $this->mower($user),
            default => $this->admin(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function admin(): array
    {
        return Cache::remember(
            $this->cacheKey('admin'),
            $this->ttl(),
            fn (): array => $this->buildAdminAnalytics()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function sales(): array
    {
        return Cache::remember(
            $this->cacheKey('sales'),
            $this->ttl(),
            fn (): array => $this->buildSalesAnalytics()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function mower(User $mower): array
    {
        return Cache::remember(
            $this->cacheKey('mower.'.$mower->id),
            $this->ttl(),
            fn (): array => $this->buildMowerAnalytics($mower)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAdminAnalytics(): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $revenueMtd = (float) DB::table('service_jobs as sj')
            ->join('clients as c', 'c.id', '=', 'sj.client_id')
            ->whereNull('sj.deleted_at')
            ->where('sj.status', JobWorkflowStatus::COMPLETED->value)
            ->where('sj.payment_status', JobOperationalPaymentStatus::RECEIVED->value)
            ->whereBetween('sj.scheduled_date', [$monthStart, $today])
            ->sum('c.charges');

        $pendingRow = DB::table('service_jobs as sj')
            ->join('clients as c', 'c.id', '=', 'sj.client_id')
            ->whereNull('sj.deleted_at')
            ->where('sj.payment_status', JobOperationalPaymentStatus::PENDING->value)
            ->whereNotIn('sj.status', [JobWorkflowStatus::COMPLETED->value])
            ->selectRaw('COUNT(*) as job_count, COALESCE(SUM(c.charges), 0) as amount')
            ->first();

        $jobsToday = Job::query()
            ->whereDate('scheduled_date', $today)
            ->whereNotIn('status', ['Cancelled'])
            ->count();

        $completedToday = Job::query()
            ->whereDate('scheduled_date', $today)
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->count();

        $completedMtd = Job::query()
            ->whereBetween('scheduled_date', [$monthStart, $today])
            ->where('status', JobWorkflowStatus::COMPLETED->value)
            ->count();

        $mowerPerformance = DB::table('job_user_assignments as jua')
            ->join('service_jobs as sj', 'sj.id', '=', 'jua.job_id')
            ->join('users as u', 'u.id', '=', 'jua.user_id')
            ->whereNull('sj.deleted_at')
            ->where('sj.status', JobWorkflowStatus::COMPLETED->value)
            ->whereBetween('sj.scheduled_date', [$monthStart, $today])
            ->groupBy('jua.user_id', 'u.name', 'u.efficiency')
            ->selectRaw('u.name as mower_name, u.efficiency, COUNT(sj.id) as completed_jobs, COALESCE(SUM(sj.consumed_time_minutes), 0) as minutes')
            ->orderByDesc('completed_jobs')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->mower_name,
                'efficiency' => $row->efficiency,
                'completed_jobs' => (int) $row->completed_jobs,
                'hours' => round(((int) $row->minutes) / 60, 1),
            ])
            ->all();

        return [
            'type' => 'admin',
            'cards' => [
                'total_revenue' => [
                    'label' => 'Total revenue (MTD)',
                    'value' => '$'.number_format($revenueMtd, 2),
                    'subtitle' => 'Completed & received payments',
                    'accent' => 'emerald',
                ],
                'pending_payments' => [
                    'label' => 'Pending payments',
                    'value' => (string) ((int) ($pendingRow->job_count ?? 0)),
                    'subtitle' => '$'.number_format((float) ($pendingRow->amount ?? 0), 2).' outstanding',
                    'accent' => 'amber',
                ],
                'jobs_today' => [
                    'label' => 'Jobs today',
                    'value' => (string) $jobsToday,
                    'subtitle' => $completedToday.' completed today',
                    'accent' => 'sky',
                ],
                'completed_jobs' => [
                    'label' => 'Completed jobs (MTD)',
                    'value' => (string) $completedMtd,
                    'subtitle' => 'Month to date',
                    'accent' => 'teal',
                ],
            ],
            'charts' => [
                'revenue_trend' => $this->revenueTrendLastDays(7),
                'jobs_by_status' => $this->jobsByStatusChart(),
            ],
            'mower_performance' => $mowerPerformance,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSalesAnalytics(): array
    {
        $statusCounts = Lead::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalLeads = (int) $statusCounts->sum();
        $converted = (int) ($statusCounts[LeadStatus::MATURE->value] ?? 0)
            + (int) ($statusCounts[LeadStatus::WON->value] ?? 0);
        $newLeads = (int) ($statusCounts[LeadStatus::NEW->value] ?? 0);
        $matureLeads = (int) ($statusCounts[LeadStatus::MATURE->value] ?? 0);

        $conversionRate = $totalLeads > 0
            ? round(($converted / $totalLeads) * 100, 1)
            : 0.0;

        $labels = [];
        $data = [];
        foreach (LeadStatus::values() as $status) {
            $labels[] = $status;
            $data[] = (int) ($statusCounts[$status] ?? 0);
        }

        return [
            'type' => 'sales',
            'cards' => [
                'conversion_rate' => [
                    'label' => 'Lead conversion rate',
                    'value' => $conversionRate.'%',
                    'subtitle' => $converted.' mature/won of '.$totalLeads.' leads',
                    'accent' => 'emerald',
                ],
                'new_leads' => [
                    'label' => 'New leads',
                    'value' => (string) $newLeads,
                    'subtitle' => 'Awaiting follow-up',
                    'accent' => 'sky',
                ],
                'mature_leads' => [
                    'label' => 'Mature leads',
                    'value' => (string) $matureLeads,
                    'subtitle' => 'Ready for conversion',
                    'accent' => 'amber',
                ],
                'follow_up' => [
                    'label' => 'Follow up',
                    'value' => (string) ($statusCounts[LeadStatus::FOLLOW_UP->value] ?? 0),
                    'subtitle' => 'Active pipeline',
                    'accent' => 'violet',
                ],
            ],
            'charts' => [
                'leads_by_status' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'Leads',
                        'data' => $data,
                        'backgroundColor' => ['#10b981', '#0ea5e9', '#f59e0b', '#6366f1', '#ef4444'],
                    ]],
                ],
                'conversion_trend' => $this->leadConversionTrendLastDays(14),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMowerAnalytics(User $mower): array
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $completed = JobWorkflowStatus::COMPLETED->value;
        $started = JobWorkflowStatus::STARTED->value;
        $hold = JobWorkflowStatus::HOLD->value;

        $stats = DB::table('job_user_assignments as jua')
            ->join('service_jobs as sj', function ($join): void {
                $join->on('sj.id', '=', 'jua.job_id')->whereNull('sj.deleted_at');
            })
            ->where('jua.user_id', $mower->id)
            ->selectRaw(
                'SUM(CASE WHEN sj.scheduled_date = ? AND sj.status != ? THEN 1 ELSE 0 END) as todays_jobs, '
                .'SUM(CASE WHEN sj.scheduled_date = ? AND sj.status = ? THEN 1 ELSE 0 END) as completed_today, '
                .'SUM(CASE WHEN sj.scheduled_date = ? AND sj.status = ? THEN COALESCE(sj.consumed_time_minutes, 0) ELSE 0 END) as minutes_today, '
                .'SUM(CASE WHEN sj.scheduled_date <= ? AND sj.status IN (?, ?) THEN 1 ELSE 0 END) as pending_jobs, '
                .'SUM(CASE WHEN sj.scheduled_date BETWEEN ? AND ? AND sj.status = ? THEN COALESCE(sj.consumed_time_minutes, 0) ELSE 0 END) as minutes_week',
                [$today, $completed, $today, $completed, $today, $completed, $today, $started, $hold, $weekStart, $today, $completed]
            )
            ->first();

        $todaysJobs = (int) ($stats->todays_jobs ?? 0);
        $completedToday = (int) ($stats->completed_today ?? 0);
        $completedMinutesToday = (int) ($stats->minutes_today ?? 0);
        $pendingJobs = (int) ($stats->pending_jobs ?? 0);
        $completedWeekMinutes = (int) ($stats->minutes_week ?? 0);

        return [
            'type' => 'mower',
            'cards' => [
                'todays_jobs' => [
                    'label' => "Today's jobs",
                    'value' => (string) $todaysJobs,
                    'subtitle' => $completedToday.' completed today',
                    'accent' => 'emerald',
                ],
                'completed_hours' => [
                    'label' => 'Completed hours (today)',
                    'value' => number_format($completedMinutesToday / 60, 1).'h',
                    'subtitle' => number_format($completedWeekMinutes / 60, 1).'h this week',
                    'accent' => 'sky',
                ],
                'pending_jobs' => [
                    'label' => 'Pending jobs',
                    'value' => (string) $pendingJobs,
                    'subtitle' => 'Started or on hold',
                    'accent' => 'amber',
                ],
            ],
            'charts' => [
                'weekly_hours' => $this->mowerWeeklyHoursChart($mower),
            ],
        ];
    }

    /**
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    private function revenueTrendLastDays(int $days): array
    {
        $labels = [];
        $data = [];
        $from = now()->subDays($days - 1)->toDateString();
        $to = now()->toDateString();

        $totals = DB::table('service_jobs as sj')
            ->join('clients as c', 'c.id', '=', 'sj.client_id')
            ->whereNull('sj.deleted_at')
            ->where('sj.status', JobWorkflowStatus::COMPLETED->value)
            ->where('sj.payment_status', JobOperationalPaymentStatus::RECEIVED->value)
            ->whereBetween('sj.scheduled_date', [$from, $to])
            ->groupBy('sj.scheduled_date')
            ->selectRaw('sj.scheduled_date as day, COALESCE(SUM(c.charges), 0) as total')
            ->pluck('total', 'day');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('M j');
            $data[] = (float) ($totals[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Revenue',
                'data' => $data,
                'backgroundColor' => 'rgba(16, 185, 129, 0.65)',
                'borderColor' => '#059669',
                'borderWidth' => 1,
            ]],
        ];
    }

    /**
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    private function jobsByStatusChart(): array
    {
        $counts = Job::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereDate('scheduled_date', '>=', now()->subDays(30)->toDateString())
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'labels' => $counts->keys()->all(),
            'datasets' => [[
                'label' => 'Jobs (30 days)',
                'data' => $counts->values()->map(fn ($v) => (int) $v)->all(),
                'backgroundColor' => ['#10b981', '#f59e0b', '#64748b', '#0ea5e9', '#ef4444'],
            ]],
        ];
    }

    /**
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    private function leadConversionTrendLastDays(int $days): array
    {
        $labels = [];
        $created = [];
        $converted = [];
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();

        $createdByDay = Lead::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $convertedByDay = Lead::query()
            ->whereIn('status', [LeadStatus::MATURE->value, LeadStatus::WON->value])
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw('DATE(updated_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('M j');
            $created[] = (int) ($createdByDay[$key] ?? 0);
            $converted[] = (int) ($convertedByDay[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New leads',
                    'data' => $created,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Converted',
                    'data' => $converted,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    /**
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    private function mowerWeeklyHoursChart(User $mower): array
    {
        $labels = [];
        $hours = [];
        $from = now()->subDays(6)->toDateString();
        $to = now()->toDateString();

        $minutesByDay = DB::table('job_user_assignments as jua')
            ->join('service_jobs as sj', function ($join): void {
                $join->on('sj.id', '=', 'jua.job_id')->whereNull('sj.deleted_at');
            })
            ->where('jua.user_id', $mower->id)
            ->where('sj.status', JobWorkflowStatus::COMPLETED->value)
            ->whereBetween('sj.scheduled_date', [$from, $to])
            ->groupBy('sj.scheduled_date')
            ->selectRaw('sj.scheduled_date as day, COALESCE(SUM(sj.consumed_time_minutes), 0) as minutes')
            ->pluck('minutes', 'day');

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            $hours[] = round(((int) ($minutesByDay[$date->toDateString()] ?? 0)) / 60, 1);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Hours',
                'data' => $hours,
                'backgroundColor' => 'rgba(14, 165, 233, 0.65)',
                'borderColor' => '#0284c7',
                'borderWidth' => 1,
            ]],
        ];
    }

    private function cacheKey(string $suffix): string
    {
        return config('mowing.cache_keys.dashboard_stats')
            .'.analytics.'
            .$suffix
            .'.g'
            .OptimizationHelper::dashboardAnalyticsGeneration();
    }

    private function ttl(): int
    {
        return \App\Support\CrmConstants::dashboardStatsTtl();
    }
}
