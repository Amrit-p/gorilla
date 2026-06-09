<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Contractor;
use App\Models\User;
use App\Repositories\ContractorRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContractorService
{
    public function __construct(
        private readonly ContractorRepository $repository,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function createContractor(User $actor, array $data): Contractor
    {
        $contractor = Contractor::query()->create([
            'name' => trim((string) $data['name']),
            'phone' => trim((string) $data['phone']),
            'email' => isset($data['email']) ? trim((string) $data['email']) : null,
        ]);

        $this->activityLogService->log($actor, 'contractor.created', 'Contractor created.', [
            'contractor_id' => $contractor->id,
            'contractor_name' => $contractor->name,
        ]);

        return $contractor;
    }

    public function updateContractor(User $actor, Contractor $contractor, array $data): Contractor
    {
        $contractor->update([
            'name' => trim((string) $data['name']),
            'phone' => trim((string) $data['phone']),
            'email' => isset($data['email']) ? trim((string) $data['email']) : null,
        ]);

        $this->activityLogService->log($actor, 'contractor.updated', 'Contractor updated.', [
            'contractor_id' => $contractor->id,
            'contractor_name' => $contractor->name,
        ]);

        return $contractor;
    }

    public function createContract(User $actor, Contractor $contractor, array $data): Contract
    {
        $contract = $contractor->contracts()->create([
            'name' => isset($data['name']) ? trim((string) $data['name']) : null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'status' => ContractStatus::Active->value,
        ]);

        $contract->statusHistories()->create([
            'status' => ContractStatus::Active->value,
            'changed_by' => $actor->id,
            'changed_at' => now(),
        ]);

        $this->activityLogService->log($actor, 'contract.created', 'Contract created.', [
            'contractor_id' => $contractor->id,
            'contract_id' => $contract->id,
        ]);

        return $contract;
    }

    public function updateContract(User $actor, Contract $contract, array $data): Contract
    {
        $contract->update([
            'name' => isset($data['name']) ? trim((string) $data['name']) : null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
        ]);

        $this->activityLogService->log($actor, 'contract.updated', 'Contract updated.', [
            'contractor_id' => $contract->contractor_id,
            'contract_id' => $contract->id,
        ]);

        return $contract;
    }

    public function updateContractStatus(User $actor, Contract $contract, ContractStatus $status): Contract
    {
        $contract->update(['status' => $status->value]);

        $contract->statusHistories()->create([
            'status' => $status->value,
            'changed_by' => $actor->id,
            'changed_at' => now(),
        ]);

        $this->activityLogService->log($actor, 'contract.status_updated', 'Contract status changed.', [
            'contractor_id' => $contract->contractor_id,
            'contract_id' => $contract->id,
            'status' => $status->value,
        ]);

        return $contract;
    }

    public function attachDocument(User $actor, Contract $contract, UploadedFile $file): ContractDocument
    {
        $path = $file->store("contract-documents/{$contract->id}", 'public');

        $document = $contract->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $this->resolveFileType((string) $file->getMimeType()),
            'uploaded_by' => $actor->id,
        ]);

        $this->activityLogService->log($actor, 'contract.document_uploaded', 'Contract document uploaded.', [
            'contract_id' => $contract->id,
            'document_id' => $document->id,
            'original_name' => $document->original_name,
        ]);

        return $document;
    }

    public function removeDocument(User $actor, ContractDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);

        $this->activityLogService->log($actor, 'contract.document_deleted', 'Contract document deleted.', [
            'contract_id' => $document->contract_id,
            'original_name' => $document->original_name,
        ]);

        $document->delete();
    }

    private function resolveFileType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if ($mime === 'application/pdf') {
            return 'pdf';
        }

        return 'document';
    }
}
