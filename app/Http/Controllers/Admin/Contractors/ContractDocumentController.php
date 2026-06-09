<?php

namespace App\Http\Controllers\Admin\Contractors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contractors\StoreContractDocumentRequest;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Contractor;
use App\Services\ContractorService;
use App\Support\CrmPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractDocumentController extends Controller
{
    public function __construct(private readonly ContractorService $service) {}

    public function store(StoreContractDocumentRequest $request, Contractor $contractor, Contract $contract): JsonResponse
    {
        abort_unless($contract->contractor_id === $contractor->id, 404);

        $document = $this->service->attachDocument(
            $request->user(),
            $contract,
            $request->file('document')
        );

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'document' => [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'file_type' => $document->file_type,
                'file_size' => $document->file_size,
            ],
        ]);
    }

    public function download(Request $request, Contractor $contractor, Contract $contract, ContractDocument $document, string $disposition = 'inline'): StreamedResponse
    {
        abort_unless($request->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);
        abort_unless($contract->contractor_id === $contractor->id, 404);
        abort_unless($document->contract_id === $contract->id, 404);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        $disposition = in_array($request->query('disposition'), ['inline', 'attachment'], true)
            ? $request->query('disposition')
            : 'inline';

        return Storage::disk('public')->response(
            $document->file_path,
            $document->original_name,
            ['Content-Disposition' => $disposition.'; filename="'.$document->original_name.'"']
        );
    }

    public function destroy(Request $request, Contractor $contractor, Contract $contract, ContractDocument $document): JsonResponse
    {
        abort_unless($request->user()?->can(CrmPermissions::MANAGE_CONTRACTORS), 403);
        abort_unless($contract->contractor_id === $contractor->id, 404);
        abort_unless($document->contract_id === $contract->id, 404);

        $this->service->removeDocument($request->user(), $document);

        return response()->json(['message' => 'Document deleted successfully.']);
    }
}
