<?php

namespace App\Services;

use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Models\User;
use App\Support\CrmRoles;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobMowerAssignmentService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function mowerWorkloads(?string $scheduledDate = null): array
    {
        $date = $scheduledDate ?: now()->toDateString();
        $activeStatuses = [JobWorkflowStatus::STARTED->value, JobWorkflowStatus::HOLD->value];

        $mowers = User::query()
            ->role(CrmRoles::MOWER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'efficiency']);

        if ($mowers->isEmpty()) {
            return [];
        }

        $mowerIds = $mowers->pluck('id')->all();

        $aggregates = DB::table('job_user_assignments as jua')
            ->join('service_jobs as sj', function ($join): void {
                $join->on('sj.id', '=', 'jua.job_id')
                    ->whereNull('sj.deleted_at');
            })
            ->whereIn('jua.user_id', $mowerIds)
            ->whereDate('sj.scheduled_date', $date)
            ->whereIn('sj.status', $activeStatuses)
            ->groupBy('jua.user_id')
            ->selectRaw('jua.user_id, COALESCE(SUM(sj.estimated_duration_minutes), 0) as assigned_minutes, COUNT(sj.id) as job_count')
            ->get()
            ->keyBy('user_id');

        return $mowers
            ->map(function (User $mower) use ($aggregates): array {
                $row = $aggregates->get($mower->id);
                $assignedMinutes = (int) ($row->assigned_minutes ?? 0);
                $factor = $this->efficiencyFactor($mower->efficiency);

                return [
                    'id' => $mower->id,
                    'name' => $mower->name,
                    'efficiency' => $mower->efficiency ?: UserEfficiency::AVERAGE->value,
                    'efficiency_factor' => $factor,
                    'assigned_minutes' => $assignedMinutes,
                    'adjusted_load' => (int) round($assignedMinutes / $factor),
                    'job_count' => (int) ($row->job_count ?? 0),
                ];
            })
            ->sortBy('adjusted_load')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function suggestMower(?string $scheduledDate, int $estimatedMinutes): ?array
    {
        $workloads = $this->mowerWorkloads($scheduledDate);
        if ($workloads === []) {
            return null;
        }

        $best = collect($workloads)->sortBy(function (array $row) use ($estimatedMinutes): float {
            return $row['adjusted_load'] + $estimatedMinutes / $row['efficiency_factor'];
        })->first();

        if (! $best) {
            return null;
        }

        return [
            'mower_id' => $best['id'],
            'mower_name' => $best['name'],
            'efficiency' => $best['efficiency'],
            'reason' => sprintf(
                'Lowest projected load (%d min assigned, %s efficiency).',
                $best['assigned_minutes'],
                $best['efficiency']
            ),
            'workloads' => $workloads,
        ];
    }

    private function efficiencyFactor(?string $efficiency): float
    {
        return match ($efficiency) {
            UserEfficiency::GOOD->value => 1.15,
            UserEfficiency::BEGINNER->value => 0.85,
            default => 1.0,
        };
    }
}
