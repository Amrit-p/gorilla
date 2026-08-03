<?php

namespace App\Services;

use App\Enums\JobWorkflowStatus;
use App\Enums\LeadStatus;
use App\Models\ActivityLog;
use App\Models\DashboardPreference;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Aggregate KPIs for dashboard cards — cached briefly to absorb refresh storms.
     * Cache clears when Lead or Job models change (see AppServiceProvider).
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $ttlSeconds = (int) config('mowing.cache.ttl.dashboard_stats_seconds', 90);

        return Cache::remember(
            config('mowing.cache_keys.dashboard_stats'),
            $ttlSeconds,
            function (): array {
                $today = now()->toDateString();
                $yesterday = now()->subDay()->toDateString();
                $monthStart = now()->startOfMonth()->toDateString();
                $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
                $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();

                return [
                    'today' => $this->periodMetrics($today, $today),
                    'yesterday' => $this->periodMetrics($yesterday, $yesterday),
                    'month_to_date' => $this->periodMetrics($monthStart, $today),
                    'pipeline' => [
                        'open_leads' => Lead::query()
                            ->whereNotIn('status', [LeadStatus::LOST->value, LeadStatus::MATURE->value, LeadStatus::WON->value])
                            ->count(),
                        'upcoming_jobs' => Job::query()
                            ->whereDate('scheduled_date', '>', $today)
                            ->whereNotIn('status', ['Completed', 'Cancelled'])
                            ->count(),
                        'employees_on_route' => $this->countEmployeesEnRouteToday(),
                    ],
                    'last_month' => $this->periodMetrics($lastMonthStart, $lastMonthEnd),
                ];
            }
        );
    }

    /**
     * Jobs scheduled for today (dashboard schedule table).
     */
    public function todaysScheduledJobs(int $limit = 15): Collection
    {
        return Job::query()
            ->select([
                'id',
                'customer_name',
                'phone',
                'client_address',
                'scheduled_date',
                'scheduled_time',
                'status',
                'payment_status',
                'payment_mode',
                'done_by_user_id',
            ])
            ->with([
                'doneByUser:id,name',
            ])
            ->whereDate('scheduled_date', now()->toDateString())
            ->whereNotIn('status', ['Cancelled'])
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Lead counts grouped by status for the pipeline summary.
     *
     * @return array<string, int>
     */
    public function leadsByStatus(): array
    {
        $ttlSeconds = (int) config('mowing.cache.ttl.dashboard_stats_seconds', 90);

        return Cache::remember(
            config('mowing.cache_keys.dashboard_stats').'.lead_status',
            $ttlSeconds,
            function (): array {
                return Lead::query()
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->all();
            }
        );
    }

    /**
     * @return array{leads: int, converted: int, jobs: int, revenue: float}
     */
    private function periodMetrics(string $fromDate, string $toDate): array
    {
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();

        return [
            'leads' => Lead::query()
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'converted' => Lead::query()
                ->whereNotNull('converted_at')
                ->whereBetween('converted_at', [$from, $to])
                ->count(),
            'clients' => Lead::query()
                ->whereNotNull('converted_at')
                ->whereBetween('converted_at', [$from, $to])
                ->count(),
            'jobs' => Job::query()
                ->whereBetween('scheduled_date', [$fromDate, $toDate])
                ->whereNotIn('status', ['Cancelled'])
                ->count(),
            'revenue' => (float) Lead::query()
                ->whereBetween('created_at', [$from, $to])
                ->sum('charges'),
        ];
    }

    /**
     * Distinct crew members actively routed for today's jobs with status En Route.
     */
    private function countEmployeesEnRouteToday(): int
    {
        $aggregate = DB::table('job_user_assignments as jua')
            ->join('service_jobs as sj', 'sj.id', '=', 'jua.job_id')
            ->whereDate('sj.scheduled_date', '=', now()->toDateString())
            ->where('sj.status', '=', 'En Route')
            ->whereNull('sj.deleted_at')
            ->selectRaw('COUNT(DISTINCT jua.user_id) as aggregate')
            ->value('aggregate');

        return (int) $aggregate;
    }

    /**
     * Recent activity timeline for dashboard — small column set + eager user.
     */
    public function recentActivity(int $limit = 8)
    {
        return ActivityLog::query()
            ->select(['id', 'user_id', 'action', 'description', 'created_at'])
            ->with(['user:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Persist dashboard UI preferences for current user.
     */
    public function updatePreferences(User $user, array $data): DashboardPreference
    {
        return DashboardPreference::query()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }

    /**
     * Fetch user preferences or return default values.
     */
    public function preferencesFor(User $user): DashboardPreference
    {
        return DashboardPreference::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'light',
                'compact_cards' => false,
                'show_activity_timeline' => true,
            ]
        );
    }

    /**
     * Three-week job schedule summary grouped by day and zone.
     * Starts from the Monday of the current week.
     *
     * @param  array{zone_id?: int|null, worker_id?: int|null, search?: string|null}  $filters
     * @return array<int, array{label: string, week_number: int, start_date: string, end_date: string, days: list<array>}>
     */
    public function threeWeekScheduleSummary(array $filters = []): array
    {
        $startDate = now()->startOfWeek(Carbon::MONDAY)->addWeeks($filters['week_offset'] ?? 0);
        $endDate = $startDate->copy()->addWeeks(3)->subDay();

        $jobs = Job::query()
            ->select(['id', 'zone_id', 'scheduled_date', 'status'])
            ->with('zone:id,name')
            ->whereBetween('scheduled_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', [
                JobWorkflowStatus::PENDING->value,
                JobWorkflowStatus::HOLD->value,
                JobWorkflowStatus::COMPLETED->value,
            ])
            ->when($filters['zone_id'] ?? null, fn ($q, $id) => $q->where('zone_id', $id))
            ->when($filters['worker_id'] ?? null, fn ($q, $id) => $q->whereHas(
                'assignedEmployees', fn ($q) => $q->where('users.id', $id)
            ))
            ->when($filters['search'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('client_address', 'like', "%{$term}%")
                        ->orWhere('customer_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhereHas('assignedEmployees', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('doneByUser', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->get();

        $jobsByDate = $jobs->groupBy(fn ($job) => $job->scheduled_date->format('Y-m-d'));

        $leads = Lead::query()
            ->select(['id', 'zone_id', 'lead_date'])
            ->with('zone:id,name')
            ->whereNotNull('lead_date')
            ->whereNull('converted_at')
            ->whereBetween('lead_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($filters['zone_id'] ?? null, fn ($q, $id) => $q->where('zone_id', $id))
            ->when($filters['search'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('client_name', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%");
                });
            })
            ->get();

        $leadsByDate = $leads->groupBy(fn ($lead) => $lead->lead_date->format('Y-m-d'));

        $weeks = [];
        for ($w = 0; $w < 3; $w++) {
            $weekStart = $startDate->copy()->addWeeks($w);
            $days = [];
            for ($d = 0; $d < 7; $d++) {
                $day = $weekStart->copy()->addDays($d);
                $key = $day->toDateString();
                $dayJobs = $jobsByDate->get($key, collect());
                $zones = $dayJobs
                    ->groupBy(fn ($j) => $j->zone?->name ?? 'Unassigned')
                    ->map->count()
                    ->toArray();

                $dayLeads = $leadsByDate->get($key, collect());
                $leadZones = $dayLeads
                    ->groupBy(fn ($l) => $l->zone?->name ?? 'Unassigned')
                    ->map->count()
                    ->toArray();

                $days[] = [
                    'date' => $key,
                    'day_name' => $day->format('l'),
                    'date_label' => $day->format('d M'),
                    'is_today' => $day->isToday(),
                    'is_past' => $day->isPast() && ! $day->isToday(),
                    'total' => $dayJobs->count(),
                    'zone_count' => count($zones),
                    'zones' => $zones,
                    'lead_total' => $dayLeads->count(),
                    'lead_zone_count' => count($leadZones),
                    'lead_zones' => $leadZones,
                ];
            }
            $weeks[] = [
                'label' => 'Week '.($w + 1),
                'week_number' => $weekStart->weekOfYear,
                'start_date' => $weekStart->format('d M'),
                'end_date' => $weekStart->copy()->addDays(6)->format('d M'),
                'days' => $days,
            ];
        }

        return $weeks;
    }

    /**
     * Company-wide backlog of jobs on hold, earliest scheduled date first.
     *
     * @return Collection<int, Job>
     */
    public function holdJobs(): Collection
    {
        return Job::query()
            ->select(['id', 'customer_name', 'phone', 'client_address', 'scheduled_date', 'scheduled_time', 'estimated_duration_minutes', 'status', 'payment_status', 'done_by_user_id'])
            ->with(['assignedEmployees:id,name', 'doneByUser:id,name'])
            ->where('status', JobWorkflowStatus::HOLD->value)
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();
    }
}
