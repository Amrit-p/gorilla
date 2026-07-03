<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Contracts\Reports\ChecklistReportInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ChecklistReportRequest;
use App\Models\Checklist;
use App\Models\User;
use App\Support\CrmRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ChecklistReportController extends Controller
{
    public function __construct(
        private readonly ChecklistReportInterface $checklistReportService,
    ) {}

    public function index(ChecklistReportRequest $request): View
    {
        $dto = $request->toDTO();
        $isMower = $request->user()->hasRole(CrmRoles::MOWER);
        $data = [
            'filters' => $dto->toArray(),
            'isMower' => $isMower,
            'checklists' => Checklist::orderBy('name')->get(['id', 'name']),
            'employees' => $isMower ? collect() : User::role(CrmRoles::label('mower'))->orderBy('name')->get(['id', 'name']),
        ];

        return view('reports.checklist.index', $data);
    }

    public function report(ChecklistReportRequest $request): JsonResponse
    {
        try {
            $dto = $request->toDTO();
            $reportData = $this->checklistReportService->generate($dto);

            return response()->json([
                'html' => view('reports.checklist.partials.table', [
                    'reportData' => $reportData,
                    'startDate' => $dto->start_date,
                    'endDate' => $dto->end_date,
                ])->render(),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => 'Failed to generate checklist report.'], 500);
        }
    }

    public function export(ChecklistReportRequest $request): Response
    {
        try {
            return $this->checklistReportService->export($request->toDTO());
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => 'Failed to export checklist report.'], 500);
        }
    }

    public function exportPdf(ChecklistReportRequest $request): Response
    {
        try {
            $dto = $request->toDTO();
            $reportData = $this->checklistReportService->generate($dto);

            return Pdf::loadView('reports.checklist.export-pdf', [
                'reportData' => $reportData,
                'startDate' => $dto->start_date,
                'endDate' => $dto->end_date,
            ])->setPaper('a4', 'landscape')
                ->download('checklist_report_'.now()->format('Y_m_d').'.pdf');
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => 'Failed to export checklist report as PDF.'], 500);
        }
    }
}
