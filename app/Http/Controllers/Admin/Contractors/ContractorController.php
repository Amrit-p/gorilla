<?php

namespace App\Http\Controllers\Admin\Contractors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contractors\StoreContractorRequest;
use App\Http\Requests\Admin\Contractors\UpdateContractorRequest;
use App\Models\Contractor;
use App\Repositories\ContractorRepository;
use App\Services\ContractorService;
use App\Services\JobManagementService;
use App\Support\CrmPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function __construct(
        private readonly ContractorRepository $repository,
        private readonly ContractorService $service,
        private readonly JobManagementService $jobManagementService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        abort_unless($request->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);

        $filters = [
            'search' => $request->string('search')->toString(),
        ];

        $contractors = $this->repository->paginatedList(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.contractors.partials.table', ['contractors' => $contractors]);
        }

        return view('admin.contractors.index', [
            'contractors' => $contractors,
            'filters' => $filters,
        ]);
    }

    public function store(StoreContractorRequest $request): JsonResponse
    {
        $this->service->createContractor($request->user(), $request->validated());

        return response()->json(['message' => 'Contractor created successfully.']);
    }

    public function show(Contractor $contractor): JsonResponse
    {
        abort_unless(auth()->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);

        return response()->json([
            'id' => $contractor->id,
            'name' => $contractor->name,
            'phone' => $contractor->phone,
            'email' => $contractor->email,
        ]);
    }

    public function detail(Request $request, Contractor $contractor): View
    {
        abort_unless($request->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);

        $contractor = $this->repository->findWithContracts($contractor);

        $filters = $this->jobFiltersFromRequest($request, $contractor->id);
        $jobs = $this->jobManagementService->paginatedJobs($filters, crm_pagination());

        return view('admin.contractors.show', array_merge(
            compact('contractor', 'jobs', 'filters'),
            $this->jobManagementService->formOptions(),
        ));
    }

    public function jobs(Request $request, Contractor $contractor): JsonResponse
    {
        abort_unless($request->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);

        $filters = $this->jobFiltersFromRequest($request, $contractor->id);
        $jobs = $this->jobManagementService->paginatedJobs($filters, crm_pagination());

        return crm_ajax_html('admin.jobs.partials.table', compact('jobs'));
    }

    /**
     * @return array<string, mixed>
     */
    private function jobFiltersFromRequest(Request $request, int $contractorId): array
    {
        return [
            'contractor_id' => $contractorId,
            'search' => $request->string('search')->toString(),
            'list_scope' => $request->string('list_scope')->toString(),
            'status' => $request->string('status')->toString(),
            'zone_id' => $request->string('zone_id')->toString(),
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

    public function update(UpdateContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $this->service->updateContractor($request->user(), $contractor, $request->validated());

        return response()->json(['message' => 'Contractor updated successfully.']);
    }
}
