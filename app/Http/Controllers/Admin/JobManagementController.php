<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobWorkflowStatus;
use App\Exports\JobsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignJobRequest;
use App\Http\Requests\Admin\StoreJobRequest;
use App\Http\Requests\Admin\UpdateJobRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Http\Requests\Admin\UploadJobImagesRequest;
use App\Models\Job;
use App\Services\JobImageManagementService;
use App\Services\JobManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function assignEmployees(AssignJobRequest $request, Job $job): JsonResponse
    {
        $this->authorize('assign', $job);
        $this->jobManagementService->assignEmployees(
            $request->user(),
            $job,
            $request->validated('employee_ids')
        );

        return response()->json(['message' => 'Mowers assigned successfully.']);
    }

    public function updateStatus(UpdateJobStatusRequest $request, Job $job): JsonResponse
    {
        $this->authorize('transitionStatus', $job);
        $job = $this->jobManagementService->updateStatus(
            $request->user(),
            $job,
            $request->validated('status')
        );

        return response()->json([
            'message' => 'Job status updated successfully.',
            'job' => $job,
        ]);
    }

    public function destroy(Request $request, Job $job): JsonResponse
    {
        $this->authorize('delete', $job);
        $this->jobManagementService->deleteJob($request->user(), $job);

        return response()->json(['message' => 'Job deleted successfully.']);
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
            'customer_type'     => $request->string('customer_type')->toString(),
            'service_type'      => $request->string('service_type')->toString(),
            'date_range_start'  => $request->input('date_range.start', ''),
            'date_range_end'    => $request->input('date_range.end', ''),
        ];
    }
}
