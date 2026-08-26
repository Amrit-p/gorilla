<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Contracts\Reports\MowerReportInterface;
use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\MowerRequestReport;
use App\Models\User;
use App\Repositories\JobRepository;
use App\Services\JobManagementService;
use App\Support\CrmRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MowerReportController extends Controller
{
    public function __construct(
        private readonly MowerReportInterface $mowerReportService,
        private readonly JobRepository $jobRepository,
        private readonly JobManagementService $jobManagementService
    ) {}

    public function index(MowerRequestReport $request): View
    {
        $dto = $request->toDTO();
        $data = [
            'filters' => $dto->toArray(),
            'hideBonusColumn' => $dto->hideBonusColumn,
            'isMower' => $request->user()->hasRole(CrmRoles::MOWER),
            ...$this->jobManagementService->formOptions(),
        ];

        return view('reports.mower.index', $data);
    }

    public function report(MowerRequestReport $request): JsonResponse
    {
        try {
            $dto = $request->toDTO();
            $reportData = $this->mowerReportService->generate($dto);

            return response()->json([
                'html' => view('reports.mower.partials.table', [
                    'reportData' => $reportData,
                    'hideBonusColumn' => $dto->hideBonusColumn,
                ])->render(),
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Failed to generate mower report.',
            ], 500);
        }
    }

    public function jobs(MowerRequestReport $request): JsonResponse
    {
        $extra = $request->validate([
            'done_by_user_id' => ['required', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $filters = array_merge(
            $request->toDTO()->toArray(),
            ['done_by_user_id' => $extra['done_by_user_id']]
        );

        $jobs = $this->jobRepository->paginatedList($filters, 15);

        return response()->json([
            'html' => view('admin.jobs.partials.table', compact('jobs'))->render(),
        ]);
    }

    /**
     * The payouts behind a mower's payout and bonus totals, rendered into the
     * payouts modal shared with the salary calculator.
     */
    public function payouts(MowerRequestReport $request): JsonResponse
    {
        $dto = $request->toDTO();

        // The payout and bonus columns are hidden from mowers, so their drill-down is too.
        abort_if($dto->hideBonusColumn, 403);

        $extra = $request->validate([
            'done_by_user_id' => ['required', 'integer'],
        ]);

        $mower = User::findOrFail((int) $extra['done_by_user_id']);
        $payouts = $this->mowerReportService->payoutsForMower($dto, $mower->id);

        return response()->json([
            'html' => view('admin.salary.calculator.partials.payouts-table', [
                'payouts' => $payouts,
                'mower' => $mower,
            ])->render(),
            'label' => $this->dateRangeLabel($dto),
            'count' => $payouts->count(),
        ]);
    }

    /** The report's active date range, for the payouts modal heading. */
    private function dateRangeLabel(MowerRequestReportDTO $dto): ?string
    {
        if (! $dto->start_date && ! $dto->end_date) {
            return null;
        }

        return ($dto->start_date?->format('d M Y') ?? '…').' – '.($dto->end_date?->format('d M Y') ?? '…');
    }

    public function export(MowerRequestReport $request): Response
    {
        try {
            return $this->mowerReportService->export($request->toDTO());
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Failed to export mower report.',
            ], 500);
        }
    }

    public function exportPdf(MowerRequestReport $request): Response
    {
        try {
            $dto = $request->toDTO();
            $reportData = $this->mowerReportService->generate($dto);

            return Pdf::loadView('reports.mower.export-pdf', [
                'reportData' => $reportData,
                'hideBonusColumn' => $dto->hideBonusColumn,
            ])->setPaper('a4', 'landscape')
                ->download('mower_report_'.now()->format('Y_m_d').'.pdf');
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Failed to export mower report as PDF.',
            ], 500);
        }
    }
}
