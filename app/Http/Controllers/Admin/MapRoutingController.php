<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignNearestEmployeeRequest;
use App\Http\Requests\Admin\MapBoundsRequest;
use App\Http\Requests\Admin\OptimizeRouteRequest;
use App\Models\Job;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Services\ActivityLogService;
use App\Services\MapRoutingService;
use App\Support\CrmRoles;
use App\Support\GoogleMapsSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\JobManagementService;

class MapRoutingController extends Controller
{
    public function __construct(
        private readonly MapRoutingService $mapRoutingService,
        private readonly ActivityLogService $activityLogService,
        private readonly JobManagementService $jobManagementService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Job::class);

        $filters = [
            'search'           => $request->input('search', ''),
            'list_scope'       => $request->input('list_scope', 'today'),
            'status'           => $request->input('status', ''),
            'zone_id'          => $request->input('zone_id', ''),
            'recurrence_id'    => $request->input('recurrence_id', ''),
            'assignment'       => $request->input('assignment', ''),
            'payment_mode'     => $request->input('payment_mode', ''),
            'payment_status'   => $request->input('payment_status', ''),
            'date_range_start' => $request->input('date_range.start', ''),
            'date_range_end'   => $request->input('date_range.end', ''),
            'equipment_type_id'     => $request->input('equipment_type_id', ''),
            'customer_type'     => $request->input('customer_type', ''),
            'service_type'      => $request->input('service_type', ''),
        ];

        return view('admin.maps.index', [
            'mapConfig'       => GoogleMapsSettings::mapPageConfig(),
            'initialDate'     => now()->toDateString(),
            'filters'         => $filters,
...$this->jobManagementService->formOptions(),
        ]);
    }

    public function jobs(MapBoundsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Job::class);
        $validated = $request->validated();
        $validated['date_range_start'] = $validated['date_range']['start'] ?? null;
        $validated['date_range_end']   = $validated['date_range']['end'] ?? null;
        unset($validated['date_range']);

        if ($request->user()->hasRole(CrmRoles::MOWER)) {
            $validated['done_by_user_id'] = $request->user()->id;
        }

        $jobs = $this->mapRoutingService->mapJobs($validated);

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
