<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Contracts\Reports\MowerReportInterface;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Http\Controllers\Controller;
use App\Models\Recurrence;
use App\Models\Zone;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Reports\MowerRequestReport;
use Illuminate\View\View;
use App\Services\JobManagementService;
use Barryvdh\DomPDF\Facade\Pdf;

class MowerReportController extends Controller
{
    public function __construct(
        private readonly MowerReportInterface $mowerReportService,
        private readonly JobRepository $jobRepository,
        private readonly JobManagementService $jobManagementService
    ) {}

    public function index(MowerRequestReport $request): View
    {
        $data = [
            'filters' => $request->toDTO()->toArray(),
            ...$this->jobManagementService->formOptions()
        ];
        return view('reports.mower.index', $data);
    }

    public function report(MowerRequestReport $request): JsonResponse
    {
        try {
            $reportData = $this->mowerReportService->generate($request->toDTO());
            return response()->json([
                'html' => view('reports.mower.partials.table', compact('reportData'))->render()
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'error' => 'Failed to generate mower report.'
            ], 500);
        }
    }

    public function jobs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'done_by_user_id'  => ['required', 'integer'],
            'status'           => ['nullable', 'string', 'in:completed,started,hold'],
            'payment_status'   => ['nullable', 'string'],
            'date_range'       => ['nullable', 'array'],
            'date_range.start' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_range.end'   => ['nullable', 'date', 'date_format:Y-m-d'],
            'equipment_type_id' => ['nullable', 'integer'],
            'customer_type'    => ['nullable', 'string', 'max:100'],
            'service_type'     => ['nullable', 'string', 'max:100'],
            'page'             => ['nullable', 'integer', 'min:1'],
        ]);

        $filters = [
            'done_by_user_id'   => $validated['done_by_user_id'],
            'status'            => $validated['status'] ?? '',
            'payment_status'    => $validated['payment_status'] ?? '',
            'date_range_start'  => $request->date('date_range.start')?->toDateString() ?? '',
            'date_range_end'    => $request->date('date_range.end')?->toDateString() ?? '',
            'equipment_type_id' => $validated['equipment_type_id'] ?? '',
            'customer_type'     => $validated['customer_type'] ?? '',
            'service_type'      => $validated['service_type'] ?? '',
        ];

        $jobs = $this->jobRepository->paginatedList($filters, 15);

        return response()->json([
            'html' => view('admin.jobs.partials.table', compact('jobs'))->render(),
        ]);
    }

    public function export(MowerRequestReport $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            return $this->mowerReportService->export($request->toDTO());
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'error' => 'Failed to export mower report.'
            ], 500);
        }
    }

    public function exportPdf(MowerRequestReport $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $reportData = $this->mowerReportService->generate($request->toDTO());

            return Pdf::loadView('reports.mower.export-pdf', compact('reportData'))
                ->setPaper('a4', 'landscape')
                ->download('mower_report_' . now()->format('Y_m_d') . '.pdf');
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'error' => 'Failed to export mower report as PDF.'
            ], 500);
        }
    }
}
