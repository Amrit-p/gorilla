<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\ActivityLog;
use App\Models\Client;
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
     * Cache clears when Lead, Client, or Job models change (see AppServiceProvider).
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
                'client_id',
                'client_address',
                'scheduled_date',
                'scheduled_time',
                'status',
                'payment_status',
                'payment_mode',
                'done_by_user_id',
            ])
            ->with([
                'client:id,name,charges,phone',
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
     * @return array{leads: int, clients: int, jobs: int, revenue: float}
     */
    private function periodMetrics(string $fromDate, string $toDate): array
    {
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();

        return [
            'leads' => Lead::query()
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'clients' => Client::query()
                ->whereBetween('created_at', [$from, $to])
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
}
