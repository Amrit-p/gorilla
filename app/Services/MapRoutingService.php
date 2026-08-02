<?php

namespace App\Services;

use App\Helpers\OptimizationHelper;
use App\Models\Job;
use App\Models\User;
use App\Support\GoogleMapsSettings;
use App\Support\QueryFilters\JobListFilter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MapRoutingService
{
    /**
     * Return map-ready jobs with computed coordinates from lead first.
     * Short-lived cache per filter combination — generation bumps when jobs/leads change.
     *
     * @param  array<string, mixed>  $filters
     */
    public function mapJobs(array $filters = []): Collection
    {
        $date = $filters['scheduled_date'] ?? null;
        $ttlSeconds = (int) config('mowing.cache.ttl.map_jobs_seconds', 30);

        // Only cache simple single-date queries; filtered queries bypass cache to stay accurate.
        $hasExtraFilters = ! empty($filters['zone_id'])
            || ! empty($filters['status'])
            || ! empty($filters['assignment'])
            || ! empty($filters['search'])
            || ! empty($filters['recurrence_id'])
            || ! empty($filters['payment_mode'])
            || ! empty($filters['payment_status'])
            || ! empty($filters['date_range_start'])
            || ! empty($filters['date_range_end'])
            || ! empty($filters['equipment_type_id'])
            || ! empty($filters['customer_type'])
            || ! empty($filters['service_type'])
            || ! empty($filters['list_scope'])
            || ! empty($filters['done_by_user_id']);

        if ($hasExtraFilters) {
            return $this->buildMapJobsPayload($filters);
        }

        $serialized = Cache::remember(
            OptimizationHelper::mapJobsCacheKey($date),
            $ttlSeconds,
            function () use ($filters): array {
                return $this->buildMapJobsPayload($filters)->all();
            }
        );

        return collect($serialized);
    }

    /**
     * Executes the eager-loaded query used by {@see mapJobs()}.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function buildMapJobsPayload(array $filters): Collection
    {
        $query = Job::query()
            ->with([
                'lead:id,client_name,address,latitude,longitude,equipment_type_id,mobile_number,email',
                'lead.equipmentType:id,name,color_code',
                'equipmentType:id,name,color_code',
                'assignedEmployees:id,name',
                'doneByUser:id,name',
            ]);

        (new JobListFilter)->apply($query, $filters);

        $jobs = $query->get();
        $lastVisitByPhone = $this->previousVisitsByPhone($jobs);

        return $jobs->map(function (Job $job) use ($lastVisitByPhone): array {
            $lat = $job->latitude ?? $job->lead?->latitude;
            $lng = $job->longitude ?? $job->lead?->longitude;
            $phone = trim((string) $job->phone);
            $previous = $phone !== '' ? ($lastVisitByPhone[$phone] ?? null) : null;

            return [
                'id' => $job->id,
                'client_name' => $job->customerDisplayName(),
                'client_address' => $job->client_address ?? $job->lead?->address,
                'client_phone' => $job->phone ?: ($job->lead?->mobile_number),
                'client_email' => $job->email ?: ($job->lead?->email),
                'show_url' => route('admin.jobs.show', $job),
                'edit_url' => route('admin.jobs.edit', $job),
                'scheduled_date' => optional($job->scheduled_date)->toDateString(),
                'scheduled_time' => $job->scheduled_time,
                'status' => $job->status,
                'priority' => $job->priority,
                'route_sequence' => $job->route_sequence,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'equipment_name' => $job->equipmentType?->name
                    ?? $job->lead?->equipmentType?->name,
                'equipment_color' => $job->equipmentType?->color_code
                    ?? $job->lead?->equipmentType?->color_code
                    ?? '#64748b',
                'assigned_employees' => collect([$job->doneByUser?->name])
                    ->filter()
                    ->concat($job->assignedEmployees->pluck('name'))
                    ->unique()
                    ->values()
                    ->all(),
                'done_by_user_id' => $job->done_by_user_id,
                'helper_employee_ids' => $job->assignedEmployees->pluck('id')->map('strval')->values()->all(),
                'last_job_date' => $previous?->scheduled_date?->toDateString(),
                'last_job_mower' => $previous?->doneByUser?->name,
            ];
        })->filter(fn (array $item): bool => ! is_null($item['lat']) && ! is_null($item['lng']))->values();
    }

    /**
     * Most recent prior visit per phone (scheduled before today), matching former Client::lastJob.
     *
     * @param  Collection<int, Job>  $jobs
     * @return array<string, Job>
     */
    private function previousVisitsByPhone(Collection $jobs): array
    {
        $phones = $jobs->pluck('phone')
            ->map(fn ($phone): string => trim((string) $phone))
            ->filter()
            ->unique()
            ->values();

        if ($phones->isEmpty()) {
            return [];
        }

        return Job::query()
            ->with('doneByUser:id,name')
            ->whereIn('phone', $phones->all())
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<', now()->toDateString())
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->get()
            ->unique(fn (Job $job): string => trim((string) $job->phone))
            ->keyBy(fn (Job $job): string => trim((string) $job->phone))
            ->all();
    }

    /**
     * Basic nearest-neighbor optimization updates route sequence.
     *
     * @param  array<int, int>  $jobIds
     */
    public function optimizeRouteSequence(array $jobIds): array
    {
        $jobs = Job::query()
            ->with(['lead:id,latitude,longitude'])
            ->whereIn('id', $jobIds)
            ->get()
            ->filter(fn (Job $job): bool => $this->jobCoordinates($job) !== null)
            ->values();

        if ($jobs->isEmpty()) {
            return [];
        }

        $ordered = collect([$jobs->shift()]);

        while ($jobs->isNotEmpty()) {
            $last = $ordered->last();
            $lastCoords = $this->jobCoordinates($last);
            if ($lastCoords === null) {
                break;
            }

            $next = $jobs->sortBy(function (Job $job) use ($lastCoords): float {
                $coords = $this->jobCoordinates($job);
                if ($coords === null) {
                    return PHP_FLOAT_MAX;
                }

                return $this->distanceKm(
                    $lastCoords['lat'],
                    $lastCoords['lng'],
                    $coords['lat'],
                    $coords['lng']
                );
            })->first();

            $ordered->push($next);
            $jobs = $jobs->reject(fn (Job $job): bool => $job->id === $next->id)->values();
        }

        $result = [];
        foreach ($ordered->values() as $index => $job) {
            $job->route_sequence = $index + 1;
            $job->save();
            $result[] = ['job_id' => $job->id, 'route_sequence' => $job->route_sequence];
        }

        return $result;
    }

    public function assignNearestEmployee(Job $job): ?User
    {
        $jobCoords = $this->jobCoordinates($job);
        if ($jobCoords === null) {
            return null;
        }

        $employees = User::query()->role(['Mowers', 'Office Manager'])->get();
        if ($employees->isEmpty()) {
            return null;
        }

        $employeeIds = $employees->pluck('id')->all();

        // Pick most recent assignment per employee in two queries (no per-user N+1).
        $orderedPairs = DB::table('job_user_assignments as jua')
            ->join('service_jobs as sj', 'sj.id', '=', 'jua.job_id')
            ->whereIn('jua.user_id', $employeeIds)
            ->whereNull('sj.deleted_at')
            ->orderByDesc('sj.scheduled_date')
            ->orderByDesc('sj.id')
            ->select(['jua.user_id', 'sj.id as job_id'])
            ->get();

        $latestJobIdByUser = [];
        foreach ($orderedPairs as $row) {
            if (! isset($latestJobIdByUser[$row->user_id])) {
                $latestJobIdByUser[$row->user_id] = $row->job_id;
            }
        }

        $referencedIds = array_values($latestJobIdByUser);
        $recentJobs = $referencedIds === []
        ? collect()
        : Job::query()
            ->with(['lead:id,latitude,longitude'])
            ->whereIn('id', $referencedIds)
            ->get()
            ->keyBy('id');

        $nearest = $employees->sortBy(function (User $employee) use ($jobCoords, $latestJobIdByUser, $recentJobs): float {
            $lastJobId = $latestJobIdByUser[$employee->id] ?? null;
            $lastJob = $lastJobId ? $recentJobs->get($lastJobId) : null;
            $lastCoords = $lastJob ? $this->jobCoordinates($lastJob) : null;

            $center = GoogleMapsSettings::defaultCenter();
            $lat = (float) ($lastCoords['lat'] ?? $center['lat']);
            $lng = (float) ($lastCoords['lng'] ?? $center['lng']);

            return $this->distanceKm($lat, $lng, $jobCoords['lat'], $jobCoords['lng']);
        })->first();

        if (! $nearest) {
            return null;
        }

        $job->assignedEmployees()->syncWithoutDetaching([
            $nearest->id => [
                'assignment_date' => $job->scheduled_date,
                'assignment_status' => 'Assigned',
            ],
        ]);
        $job->status = 'Assigned';
        $job->save();

        return $nearest;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function jobCoordinates(Job $job): ?array
    {
        $lat = $job->latitude ?? $job->lead?->latitude;
        $lng = $job->longitude ?? $job->lead?->longitude;

        if ($lat === null || $lng === null) {
            return null;
        }

        return ['lat' => (float) $lat, 'lng' => (float) $lng];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
