<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignNearestEmployeeRequest;
use App\Http\Requests\Admin\MapBoundsRequest;
use App\Http\Requests\Admin\OptimizeRouteRequest;
use App\Models\Job;
use App\Services\ActivityLogService;
use App\Services\MapRoutingService;
use App\Support\GoogleMapsSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapRoutingController extends Controller
{
    public function __construct(
        private readonly MapRoutingService $mapRoutingService,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Job::class);

        return view('admin.maps.index', [
            'mapConfig' => GoogleMapsSettings::mapPageConfig(),
            'initialDate' => now()->toDateString(),
        ]);
    }

    public function jobs(MapBoundsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Job::class);
        $jobs = $this->mapRoutingService->mapJobs($request->validated('scheduled_date'));

        return response()->json(['jobs' => $jobs]);
    }

    public function optimize(OptimizeRouteRequest $request): JsonResponse
    {
        $jobIds = $request->validated('job_ids');
        foreach (Job::query()->whereIn('id', $jobIds)->get() as $job) {
            $this->authorize('assign', $job);
        }

        $optimized = $this->mapRoutingService->optimizeRouteSequence($jobIds);

        $this->activityLogService->log($request->user(), 'map.route_optimized', 'Route sequence optimized.', [
            'count' => count($optimized),
        ]);

        return response()->json([
            'message' => 'Route sequence optimized successfully.',
            'optimized' => $optimized,
        ]);
    }

    public function assignNearest(AssignNearestEmployeeRequest $request): JsonResponse
    {
        $job = Job::query()->with('lead:id,latitude,longitude')->findOrFail($request->validated('job_id'));
        $this->authorize('assign', $job);
        $employee = $this->mapRoutingService->assignNearestEmployee($job);

        if (! $employee) {
            return response()->json(['message' => 'No nearest employee could be assigned for this job.'], 422);
        }

        $this->activityLogService->log($request->user(), 'map.nearest_employee_assigned', 'Nearest employee assigned.', [
            'job_id' => $job->id,
            'employee_id' => $employee->id,
        ]);

        return response()->json([
            'message' => "Nearest employee {$employee->name} assigned successfully.",
        ]);
    }
}
