<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobWorkflowStatus;
use App\Exports\JobsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignJobRequest;
use App\Http\Requests\Admin\ScheduleJobsRequest;
use App\Http\Requests\Admin\StoreJobRequest;
use App\Http\Requests\Admin\UpdateJobRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Http\Requests\Admin\UploadJobImagesRequest;
use App\Models\Job;
use App\Models\MowerRemark;
use App\Services\JobImageManagementService;
use App\Services\JobManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobManagementController extends Controller
{
    public function __construct(
        private readonly JobManagementService $jobManagementService,
        private readonly JobImageManagementService $jobImageManagementService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $filters = $this->exportFilters($request);
        $jobs = $this->jobManagementService->paginatedJobs(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.jobs.partials.table', compact('jobs'));
        }

        return view('admin.jobs.index', array_merge(
            ['jobs' => $jobs, 'filters' => $filters],
            $this->jobManagementService->formOptions()
        ));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Job::class);

        $clientId = $request->integer('client_id') ?: null;

        return view('admin.jobs.create', $this->jobManagementService->formOptions($clientId));
    }

    public function store(StoreJobRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Job::class);
        $job = $this->jobManagementService->createJob(
            $request->user(),
            $request->validated(),
            $request->file('images', [])
        );
        if (!$job) {
            return response()->json(['message' => 'Failed to create job. Please try again.'], 500);
        }
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job created successfully.',
                'job' => $job,
                'redirect' => route('admin.jobs.show', $job),
            ], 201);
        }

        return redirect()
            ->route('admin.jobs.show', $job)
            ->with('success', 'Job created successfully.');
    }

    public function show(Job $job): View
    {
        $this->authorize('view', $job);

        $job = $this->jobManagementService->findForShow($job->id) ?? $job;
        $timeline = $this->jobManagementService->jobTimeline($job);

        $jobImages = $this->jobImageManagementService->presentAllForJob($job);

        return view('admin.jobs.show', array_merge(
            [
                'job' => $job,
                'timeline' => $timeline,
                'attachedImages' => $this->jobImageManagementService->presentAttachedImages($job),
                'beforeImages' => $jobImages['before'],
                'afterImages' => $jobImages['after'],
            ],
            $this->jobManagementService->formOptions($job->client_id)
        ));
    }

    public function edit(Job $job): View
    {
        $this->authorize('update', $job);

        $job->load(['assignedEmployees:id,name,efficiency', 'client']);

        return view('admin.jobs.edit', array_merge(
            ['job' => $job],
            $this->jobManagementService->formOptions($job->client_id)
        ));
    }

    public function update(UpdateJobRequest $request, Job $job): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $job);

        $images = $request->file('images', []);
        if ($images !== []) {
            $this->authorize('uploadImages', $job);
        }

        $job = $this->jobManagementService->updateJob(
            $request->user(),
            $job,
            $request->validated(),
            $images
        );
        if($job === null) {
            return response()->json(['message' => 'Failed to update job. Please try again.'], 500);
        }
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job updated successfully.',
                'job' => $job,
                'redirect' => route('admin.jobs.show', $job),
            ]);
        }

        return redirect()
            ->route('admin.jobs.show', $job)
            ->with('success', 'Job updated successfully.');
    }

    public function mowerSuggestions(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $validated = $request->validate([
            'scheduled_date' => ['nullable', 'date'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ]);

        $suggestion = $this->jobManagementService->suggestMower(
            $validated['scheduled_date'] ?? now()->toDateString(),
            (int) $validated['estimated_duration_minutes']
        );

        return response()->json([
            'suggestion' => $suggestion,
            'workloads' => $this->jobManagementService->mowerWorkloads($validated['scheduled_date'] ?? null),
        ]);
    }

    public function mowerWorkloads(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $date = $request->validate(['scheduled_date' => ['nullable', 'date']])['scheduled_date'] ?? null;

        return response()->json([
            'workloads' => $this->jobManagementService->mowerWorkloads($date),
        ]);
    }

    public function clientRemarks(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $clientId = (int) $request->query('client_id');

        if (!$clientId) {
            return response()->json(['lastRemark' => null, 'allRemarks' => []]);
        }

        $remarks = MowerRemark::with('user:id,name')
            ->where('client_id', $clientId)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'description' => $r->description,
                'user_name'   => $r->user?->name ?? 'Unknown',
                'created_at'  => $r->created_at->format('M j, Y g:i A'),
            ]);

        return response()->json([
            'lastRemark' => $remarks->first(),
            'allRemarks' => $remarks->all(),
        ]);
    }

    public function uploadImages(UploadJobImagesRequest $request, Job $job): JsonResponse
    {
        $this->authorize('uploadImages', $job);
        $this->jobManagementService->appendJobImages(
            $request->user(),
            $job,
            $request->file('images', [])
        );

        return response()->json([
            'message' => 'Job images uploaded successfully.',
            'attached_images' => $job->fresh()->attached_images,
        ]);
    }


    public function bulkAssignEmployees(AssignJobRequest $request): JsonResponse
    {
        $jobs = $this->jobsForBulkAction($request->validated('job_ids'), 'assign');

        $updatedJobs = $this->jobManagementService->assignEmployeesToJobs(
            $request->user(),
            $jobs,
            $request->validated('done_by_user_id', null),
            $request->validated('employee_ids', []),
        );

        if ($updatedJobs->count() !== $jobs->count()) {
            return response()->json(['message' => 'Failed to assign selected jobs.'], 500);
        }

        return response()->json([
            'message' => $updatedJobs->count() === 1
                ? 'Mowers assigned successfully.'
                : 'Mowers assigned to selected jobs successfully.',
            'updated_count' => $updatedJobs->count(),
        ]);
    }


    public function bulkUpdateStatus(UpdateJobStatusRequest $request): JsonResponse
    {
        $jobs = $this->jobsForBulkAction($request->validated('job_ids'), 'transitionStatus');
        $updatedJobs = $this->jobManagementService->updateJobsStatus(
            $request->user(),
            $jobs,
            $request->validated('status')
        );

        return response()->json([
            'message' => $updatedJobs->count() === 1
                ? 'Job status updated successfully.'
                : 'Selected job statuses updated successfully.',
            'updated_count' => $updatedJobs->count(),
        ]);
    }


    public function bulkScheduleJobs(ScheduleJobsRequest $request): JsonResponse
    {
        $jobs = $this->jobsForBulkAction($request->validated('job_ids'), 'update');
        $updatedJobs = $this->jobManagementService->scheduleJobs(
            $request->user(),
            $jobs,
            $request->validated('scheduled_date'),
            $request->validated('scheduled_time'),
        );

        return response()->json([
            'message' => $updatedJobs->count() === 1
                ? 'Job rescheduled successfully.'
                : 'Selected jobs rescheduled successfully.',
            'updated_count' => $updatedJobs->count(),
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['integer', 'distinct'],
        ]);

        $jobs = $this->jobsForBulkAction($validated['job_ids'], 'delete');
        $deletedCount = $this->jobManagementService->deleteJobs($request->user(), $jobs);

        return response()->json([
            'message' => $deletedCount === 1
                ? 'Job deleted successfully.'
                : 'Selected jobs deleted successfully.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Job::class);

        $jobs = $this->jobManagementService->exportJobs($this->exportFilters($request));

        return (new JobsExport($jobs))->download('jobs-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', Job::class);

        $jobs = $this->jobManagementService->exportJobs($this->exportFilters($request));

        return Pdf::loadView('admin.jobs.partials.export-pdf', compact('jobs'))
            ->setPaper('a3', 'landscape')
            ->download('jobs-' . now()->format('Y-m-d') . '.pdf');
    }

    public function updateRemarks(Request $request, Job $job): JsonResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'special_remarks' => ['nullable', 'string'],
            'internal_notes'  => ['nullable', 'string'],
        ]);

        $job->update($validated);

        return response()->json(['message' => 'Remarks updated successfully.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('manage-job-records');

        $ids = $request->validate([
            'ordered_ids'   => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['required', 'integer', 'exists:service_jobs,id'],
        ])['ordered_ids'];

        foreach ($ids as $position => $id) {
            Job::query()->where('id', $id)->update(['numeric_priority' => $position + 1]);
        }

        return response()->json(['message' => 'Order saved.']);
    }

    private function exportFilters(Request $request): array
    {
        return [
            'search'         => $request->string('search')->toString(),
            'list_scope'     => $request->string('list_scope')->toString(),
            'status'         => $request->string('status')->toString(),
            'priority'       => $request->string('priority')->toString(),
            'zone_id'        => $request->string('zone_id')->toString(),
            'client_id'      => $request->string('client_id')->toString(),
            'recurrence_id'  => $request->string('recurrence_id')->toString(),
            'assignment'     => $request->string('assignment')->toString(),
            'payment_mode'      => $request->string('payment_mode')->toString(),
            'payment_status'    => $request->string('payment_status')->toString(),
            'equipment_type_id' => $request->string('equipment_type_id')->toString(),
            'job_level_id'      => $request->string('job_level_id')->toString(),
            'customer_type'     => $request->string('customer_type')->toString(),
            'service_type'      => $request->string('service_type')->toString(),
            'date_range_start'  => $request->input('date_range.start', ''),
            'date_range_end'    => $request->input('date_range.end', ''),
        ];
    }

    /**
     * @param  array<int, int>  $jobIds
     * @return Collection<int, Job>
     */
    private function jobsForBulkAction(array $jobIds, string $ability): Collection
    {
        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));

        $jobs = Job::query()
            ->whereIn('id', $jobIds)
            ->get()
            ->keyBy('id');

        if ($jobs->count() !== count($jobIds)) {
            abort(422, 'One or more selected jobs could not be found.');
        }

        $orderedJobs = collect($jobIds)->map(fn (int $jobId): Job => $jobs->get($jobId));
        $orderedJobs->each(fn (Job $job) => $this->authorize($ability, $job));

        return $orderedJobs;
    }
}
