<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryReceipt;
use App\Repositories\SalaryReceiptRepository;
use App\Services\SalaryCalculatorService;
use App\Support\CrmRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SalaryReceiptController extends Controller
{
    public function __construct(
        private readonly SalaryReceiptRepository $salaryReceiptRepository,
        private readonly SalaryCalculatorService $salaryCalculatorService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', SalaryReceipt::class);

        $filters = $this->extractFilters($request);

        $receipts = $this->salaryReceiptRepository->paginatedList(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.salary.receipts.partials.table', compact('receipts', 'filters'));
        }

        return view('admin.salary.receipts.index', [
            'receipts' => $receipts,
            'filters' => $filters,
            'mowers' => $this->salaryCalculatorService->mowers(),
        ]);
    }

    public function show(SalaryReceipt $salaryReceipt): View
    {
        $this->authorize('view', $salaryReceipt);

        return view('admin.salary.receipts.show', [
            'receipt' => $salaryReceipt->load(['mower', 'generatedBy']),
        ]);
    }

    public function exportPdf(SalaryReceipt $salaryReceipt): Response
    {
        $this->authorize('view', $salaryReceipt);

        $receipt = $salaryReceipt->load(['mower', 'generatedBy']);

        return Pdf::loadView('admin.salary.receipts.partials.export-pdf', compact('receipt'))
            ->setPaper('a4', 'portrait')
            ->download("salary-receipt-{$receipt->id}.pdf");
    }

    public function destroy(Request $request, SalaryReceipt $salaryReceipt): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $salaryReceipt);

        $this->salaryCalculatorService->deleteReceipt($salaryReceipt);

        if (crm_wants_partial($request)) {
            return response()->json(['message' => 'Salary receipt deleted.']);
        }

        return redirect()
            ->route('admin.salary-receipts.index')
            ->with('success', 'Salary receipt deleted.');
    }

    private function extractFilters(Request $request): array
    {
        $user = $request->user();

        $mowerId = $user->hasRole(CrmRoles::MOWER)
            ? (string) $user->id
            : $request->input('mower_id', '');

        return [
            'search' => $request->string('search')->toString(),
            'mower_id' => $mowerId,
            'period_type' => $request->input('period_type', ''),
            'date_range_start' => $request->input('date_range.start', ''),
            'date_range_end' => $request->input('date_range.end', ''),
        ];
    }
}
