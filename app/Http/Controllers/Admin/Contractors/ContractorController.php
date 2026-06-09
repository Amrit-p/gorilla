<?php

namespace App\Http\Controllers\Admin\Contractors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contractors\StoreContractorRequest;
use App\Http\Requests\Admin\Contractors\UpdateContractorRequest;
use App\Models\Contractor;
use App\Repositories\ContractorRepository;
use App\Services\ContractorService;
use App\Support\CrmPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function __construct(
        private readonly ContractorRepository $repository,
        private readonly ContractorService $service
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

        return view('admin.contractors.show', compact('contractor'));
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor): JsonResponse
    {
        $this->service->updateContractor($request->user(), $contractor, $request->validated());

        return response()->json(['message' => 'Contractor updated successfully.']);
    }
}
