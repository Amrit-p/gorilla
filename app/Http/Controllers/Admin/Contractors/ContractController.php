<?php

namespace App\Http\Controllers\Admin\Contractors;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contractors\StoreContractRequest;
use App\Http\Requests\Admin\Contractors\UpdateContractRequest;
use App\Http\Requests\Admin\Contractors\UpdateContractStatusRequest;
use App\Models\Contract;
use App\Models\Contractor;
use App\Services\ContractorService;
use App\Support\CrmPermissions;
use Illuminate\Http\JsonResponse;

class ContractController extends Controller
{
    public function __construct(private readonly ContractorService $service) {}

    public function store(StoreContractRequest $request, Contractor $contractor): JsonResponse
    {
        $contract = $this->service->createContract($request->user(), $contractor, $request->validated());

        return response()->json([
            'message' => 'Contract created successfully.',
            'contract_id' => $contract->id,
        ]);
    }

    public function show(Contractor $contractor, Contract $contract): JsonResponse
    {
        abort_unless(auth()->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);
        abort_unless($contract->contractor_id === $contractor->id, 404);

        return response()->json([
            'id' => $contract->id,
            'name' => $contract->name,
            'start_date' => $contract->start_date?->format('Y-m-d'),
            'end_date' => $contract->end_date?->format('Y-m-d'),
            'status' => $contract->status?->value,
        ]);
    }

    public function update(UpdateContractRequest $request, Contractor $contractor, Contract $contract): JsonResponse
    {
        abort_unless($contract->contractor_id === $contractor->id, 404);

        $this->service->updateContract($request->user(), $contract, $request->validated());

        return response()->json(['message' => 'Contract updated successfully.']);
    }

    public function updateStatus(UpdateContractStatusRequest $request, Contractor $contractor, Contract $contract): JsonResponse
    {
        abort_unless($contract->contractor_id === $contractor->id, 404);

        $status = ContractStatus::from($request->validated('status'));
        $this->service->updateContractStatus($request->user(), $contract, $status);

        return response()->json(['message' => 'Contract status updated successfully.']);
    }
}
