<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobWorkflowStatus;
use App\Exports\JobsExport;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureJobIsNotVerified;
use App\Http\Requests\Admin\AssignJobRequest;
use App\Http\Requests\Admin\ScheduleJobsRequest;
use App\Http\Requests\Admin\StoreJobRequest;
use App\Http\Requests\Admin\UpdateJobRequest;
use App\Http\Requests\Admin\UpdateJobStatusRequest;
use App\Http\Requests\Admin\UploadJobImagesRequest;
use App\Models\Contract;
use App\Models\Job;
use App\Services\ClientStatisticsService;
use App\Services\JobImageManagementService;
use App\Services\JobManagementService;
use App\Support\CrmConstants;
use App\Support\CrmPermissions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobManagementController extends Controller
{
    public function __construct(
        private readonly JobManagementService $jobManagementService,
        private readonly JobImageManagementService $jobImageManagementService,
        private readonly ClientStatisticsService $clientStatisticsService
    ) {
        $this->middleware(EnsureJobIsNotVerified::class)->only([
            'edit',
            'update',
            'updateRemarks',
            'uploadImages',
        ]);
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $filters = $this->exportFilters($request);

        // The "Deleted jobs" scope (soft-deleted records) is restricted to administrators.
        if ($filters['list_scope'] === CrmConstants::JOB_LIST_SCOPE_DELETED
            && ! CrmPermissions::isAdministrator($request->user())) {
            $filters['list_scope'] = '';
        }

        $pageSize = crm_per_page($request);

        $jobs = $this->jobManagementService->paginatedJobs($filters, $pageSize['per_page']);

        $tableData = [
            'jobs' => $jobs,
            'perPage' => $pageSize['value'],
            'infiniteScroll' => $pageSize['infinite'],
        ];

        if (crm_wants_partial($request)) {
            // Infinite scroll appends the next chunk onto the table already on
            // screen, so it asks for the rows alone; everything else (filters,
            // page links, page-size change) re-renders the whole table.
            if ($request->boolean('rows_only')) {
                return crm_ajax_html('admin.jobs.partials.rows', $tableData + ['showEmptyState' => false]);
            }

            // The mobile search modal renders job results as cards instead of
            // table rows.
            if ($request->boolean('cards_only')) {
                return crm_ajax_html('admin.jobs.partials.cards', $tableData);
            }

            return crm_ajax_html('admin.jobs.partials.table', $tableData);
        }

        return view('admin.jobs.index', array_merge(
            $tableData,
            ['filters' => $filters],
            $this->jobManagementService->formOptions()
        ));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Job::class);

        return view('admin.jobs.create', $this->jobManagementService->formOptions());
    }

    public function store(StoreJobRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Job::class);
        $job = $this->jobManagementService->createJob(
            $request->user(),
            $request->validated(),
            $request->file('images', [])
        );
        if (! $job) {
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

        $customerHistory = $this->clientStatisticsService->forJobShowSidebar($job, $job->id);

        return view('admin.jobs.show', array_merge(
            [
                'job' => $job,
                'timeline' => $timeline,
                'attachedImages' => $this->jobImageManagementService->presentAttachedImages($job),
                'beforeImages' => $jobImages['before'],
                'afterImages' => $jobImages['after'],
                'customerHistory' => $customerHistory,
            ],
            $this->jobManagementService->formOptions()
        ));
    }

    public function customerDetails(Job $job): View
    {
        $this->authorize('view', $job);

        $job = $this->jobManagementService->findForShow($job->id) ?? $job;
        $details = $this->clientStatisticsService->forCustomerDetailsModal($job);

        return view('admin.jobs.partials.customer-details-modal-body', $details);
    }

    public function edit(Job $job): View
    {
        $this->authorize('update', $job);

        $job->load(['assignedEmployees:id,name,efficiency']);

        return view('admin.jobs.edit', array_merge(
            ['job' => $job],
            $this->jobManagementService->formOptions()
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
        if ($job === null) {
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

        $jobId = (int) $request->query('job_id');
        $job = $jobId ? Job::query()->find($jobId, ['id', 'mower_remarks', 'updated_at']) : null;

        if (! $job || ! $job->mower_remarks) {
            return response()->json(['lastRemark' => null, 'allRemarks' => []]);
        }

        $remark = [
            'description' => $job->mower_remarks,
            'user_name' => null,
            'created_at' => $job->updated_at->format('M j, Y g:i A'),
        ];

        return response()->json([
            'lastRemark' => $remark,
            'allRemarks' => [$remark],
        ]);
    }

    public function clientHistory(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $jobId = (int) $request->query('job_id');
        $phone = trim((string) $request->query('phone', ''));
        $email = trim((string) $request->query('email', ''));
        $excludeJobId = (int) $request->query('exclude_job_id', 0);

        $sourceJob = $jobId ? Job::query()->find($jobId) : null;
        if ($sourceJob) {
            $phone = $phone !== '' ? $phone : trim((string) $sourceJob->phone);
            $email = $email !== '' ? $email : trim((string) $sourceJob->email);
        }

        if ($phone === '' && $email === '') {
            return response()->json([
                'accounting_level' => $sourceJob?->accountingLevel?->name,
                'service_history' => [],
                'mower_remarks' => [],
            ]);
        }

        $pastJobs = Job::with(['doneByUser:id,name', 'accountingLevel:id,name'])
            ->when($phone !== '', fn ($q) => $q->where('phone', $phone))
            ->when($phone === '' && $email !== '', fn ($q) => $q->where('email', $email))
            ->when($excludeJobId, fn ($q) => $q->where('id', '!=', $excludeJobId))
            ->whereNotNull('scheduled_date')
            ->orderByDesc('scheduled_date')
            ->limit(8)
            ->get();

        $serviceHistory = $pastJobs->map(fn ($j) => [
            'id' => $j->id,
            'date' => $j->scheduled_date->format('d M Y'),
            'status' => $j->status ?? '—',
            'done_by' => $j->doneByUser?->name ?? '—',
            'office_remarks' => $j->office_remarks,
            'internal_notes' => $j->internal_notes,
        ])->values()->all();

        $mowerRemarkJobs = $sourceJob ? $pastJobs->push($sourceJob) : $pastJobs;

        $mowerRemarks = $mowerRemarkJobs
            ->filter(fn ($j) => filled($j->mower_remarks))
            ->sortByDesc(fn ($j) => $j->scheduled_date)
            ->map(fn ($j) => [
                'id' => $j->id,
                'description' => $j->mower_remarks,
                'created_by' => $j->doneByUser?->name ?? '—',
                'created_at' => $j->scheduled_date?->format('d M Y'),
            ])->values()->all();

        return response()->json([
            'accounting_level' => $sourceJob?->accountingLevel?->name ?? $pastJobs->first()?->accountingLevel?->name,
            'service_history' => $serviceHistory,
            'mower_remarks' => $mowerRemarks,
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

    public function activeContracts(): JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $contracts = Contract::query()
            ->with('contractor:id,name,phone,email')
            ->active()
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->orderBy('name')
            ->get(['id', 'contractor_id', 'name', 'start_date', 'end_date', 'status']);

        return response()->json([
            'contracts' => $contracts->map(fn (Contract $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'start_date' => $c->start_date?->format('d M Y'),
                'end_date' => $c->end_date?->format('d M Y'),
                'contractor' => [
                    'id' => $c->contractor->id,
                    'name' => $c->contractor->name,
                    'phone' => $c->contractor->phone,
                    'email' => $c->contractor->email,
                ],
            ]),
        ]);
    }

    public function bulkAssignEmployees(AssignJobRequest $request): JsonResponse
    {
        $jobs = $this->jobsForBulkAction($request->validated('job_ids'), 'assign');

        $eligible = $jobs->reject(fn (Job $job): bool => $job->isVerified())->values();

        if ($eligible->isEmpty()) {
            return response()->json(['message' => 'The selected job(s) have been verified and employees cannot be assigned.'], 422);
        }

        $updatedJobs = $this->jobManagementService->assignEmployeesToJobs(
            $request->user(),
            $eligible,
            $request->validated('done_by_user_id', null),
            $request->validated('employee_ids', []),
        );

        if ($updatedJobs->count() !== $eligible->count()) {
            return response()->json(['message' => 'Failed to assign selected jobs.'], 500);
        }

        return response()->json([
            'message' => $updatedJobs->count() === 1
                ? 'Mowers assigned successfully.'
                : 'Mowers assigned to selected jobs successfully.',
            'updated_count' => $updatedJobs->count(),
        ]);
    }

    public function bulkAssignContract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['integer', 'distinct', 'exists:service_jobs,id'],
            'contract_id' => ['nullable', 'integer', 'exists:contracts,id'],
        ]);

        $jobs = $this->jobsForBulkAction($validated['job_ids'], 'assign');

        $eligible = $jobs->reject(fn (Job $job): bool => $job->isVerified())->values();

        if ($eligible->isEmpty()) {
            return response()->json(['message' => 'The selected job(s) have been verified and a contract cannot be assigned.'], 422);
        }

        Job::query()->whereIn('id', $eligible->pluck('id'))->update(['contract_id' => $validated['contract_id']]);

        return response()->json([
            'message' => $eligible->count() === 1
                ? 'Contract assigned successfully.'
                : 'Contract assigned to selected jobs successfully.',
        ]);
    }

    public function bulkUpdateStatus(UpdateJobStatusRequest $request): JsonResponse
    {
        $jobs = $this->jobsForBulkAction($request->validated('job_ids'), 'transitionStatus');

        $eligible = $jobs->reject(fn (Job $job): bool => $job->isVerified())->values();

        if ($eligible->isEmpty()) {
            return response()->json(['message' => 'The selected job(s) have been verified and their status cannot be changed.'], 422);
        }

        $updatedJobs = $this->jobManagementService->updateJobsStatus(
            $request->user(),
            $eligible,
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

        $eligible = $jobs->reject(fn (Job $job): bool => $job->isVerified())->values();

        if ($eligible->isEmpty()) {
            return response()->json(['message' => 'The selected job(s) have been verified and cannot be rescheduled.'], 422);
        }

        $updatedJobs = $this->jobManagementService->scheduleJobs(
            $request->user(),
            $eligible,
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

        $eligible = $jobs->reject(fn (Job $job): bool => $job->isVerified())->values();

        if ($eligible->isEmpty()) {
            return response()->json(['message' => 'The selected job(s) have been verified and cannot be deleted.'], 422);
        }

        $deletedCount = $this->jobManagementService->deleteJobs($request->user(), $eligible);

        return response()->json([
            'message' => $deletedCount === 1
                ? 'Job deleted successfully.'
                : 'Selected jobs deleted successfully.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function bulkRestore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['integer', 'distinct'],
        ]);

        $jobs = $this->jobsForBulkAction($validated['job_ids'], 'restore', withTrashed: true);
        $restoredCount = $this->jobManagementService->restoreJobs($request->user(), $jobs);

        return response()->json([
            'message' => $restoredCount === 1
                ? 'Job restored successfully.'
                : 'Selected jobs restored successfully.',
            'restored_count' => $restoredCount,
        ]);
    }

    public function bulkForceDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['integer', 'distinct'],
        ]);

        $jobs = $this->jobsForBulkAction($validated['job_ids'], 'forceDelete', withTrashed: true);

        $eligible = $jobs->reject(fn (Job $job): bool => $job->isVerified())->values();

        if ($eligible->isEmpty()) {
            return response()->json(['message' => 'The selected job(s) have been verified and cannot be permanently deleted.'], 422);
        }

        $deletedCount = $this->jobManagementService->forceDeleteJobs($request->user(), $eligible);

        return response()->json([
            'message' => $deletedCount === 1
                ? 'Job permanently deleted.'
                : 'Selected jobs permanently deleted.',
            'deleted_count' => $deletedCount,
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Job::class);

        $jobs = $this->jobManagementService->exportJobs($this->exportFilters($request));

        return (new JobsExport($jobs))->download('jobs-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', Job::class);

        $jobs = $this->jobManagementService->exportJobs($this->exportFilters($request));

        return Pdf::loadView('admin.jobs.partials.export-pdf', compact('jobs'))
            ->setPaper('a3', 'landscape')
            ->download('jobs-'.now()->format('Y-m-d').'.pdf');
    }

    public function updateRemarks(Request $request, Job $job): JsonResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'office_remarks' => ['nullable', 'string'],
        ]);

        $job->update($validated);

        return response()->json(['message' => 'Remarks updated successfully.']);
    }

    // single-job verify removed; use bulkVerify for single and bulk operations

    public function bulkVerify(Request $request): JsonResponse
    {
        $verified = $request->boolean('verified', true);

        $validated = $request->validate([
            'job_ids' => ['required', 'array', 'min:1'],
            'job_ids.*' => ['integer', 'distinct'],
            'verified' => ['sometimes', 'boolean'],
            'verified_date' => [Rule::requiredIf($verified), 'nullable', 'date'],
            'verified_amount' => [Rule::requiredIf($verified), 'nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $jobs = $this->jobsForBulkAction($validated['job_ids'], 'verify');

        if ($verified) {
            $eligible = $jobs->filter(fn (Job $job): bool => $this->jobVerificationErrors($job) === [])->values();

            if ($eligible->isEmpty()) {
                return response()->json([
                    'message' => 'None of the selected jobs can be verified. Jobs must be Completed.',
                ], 422);
            }

            $this->jobManagementService->verifyJobs($request->user(), $eligible, true, [
                'verified_date' => $validated['verified_date'],
                'verified_amount' => $validated['verified_amount'],
                'comment' => $validated['comment'] ?? null,
            ]);
        } else {
            // Un-verify selected jobs (no precondition checks)
            $eligible = $jobs;
            $this->jobManagementService->verifyJobs($request->user(), $jobs, false);
        }

        $skipped = $jobs->count() - $eligible->count();
        $message = $eligible->count() === 1
            ? ($verified ? 'Job verified successfully.' : 'Job verification removed.')
            : ($verified ? $eligible->count().' jobs verified successfully.' : $eligible->count().' job verifications removed.');
        if ($skipped > 0 && $verified) {
            $message .= ' '.$skipped.' skipped (not Completed).';
        }

        return response()->json([
            'message' => $message,
            'verified_count' => $eligible->count(),
            'skipped_count' => $skipped,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('manage-job-records');

        $ids = $request->validate([
            'ordered_ids' => ['required', 'array', 'min:1'],
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
            'search' => $request->string('search')->toString(),
            'list_scope' => $request->string('list_scope')->toString(),
            'status' => $request->string('status')->toString(),
            'priority' => $request->string('priority')->toString(),
            'contractor_id' => $request->string('contractor_id')->toString(),
            'recurrence_id' => $request->string('recurrence_id')->toString(),
            'assignment' => $request->string('assignment')->toString(),
            'payment_mode' => $request->string('payment_mode')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
            'equipment_type_id' => $request->string('equipment_type_id')->toString(),
            'job_level_id' => $request->string('job_level_id')->toString(),
            'customer_type' => $request->string('customer_type')->toString(),
            'service_type' => $request->string('service_type')->toString(),
            'date_range_start' => $request->input('date_range.start', ''),
            'date_range_end' => $request->input('date_range.end', ''),
        ];
    }

    /**
     * Unmet verification preconditions for a job, as human-readable phrases.
     *
     * @return array<int, string>
     */
    private function jobVerificationErrors(Job $job): array
    {
        $unmet = [];

        if ($job->status !== JobWorkflowStatus::COMPLETED->value) {
            $unmet[] = 'the job status is Completed';
        }

        return $unmet;
    }

    /**
     * @param  array<int, int>  $jobIds
     * @return Collection<int, Job>
     */
    private function jobsForBulkAction(array $jobIds, string $ability, bool $withTrashed = false): Collection
    {
        $jobIds = array_values(array_unique(array_map('intval', $jobIds)));

        $jobs = Job::query()
            ->when($withTrashed, fn ($query) => $query->onlyTrashed())
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
