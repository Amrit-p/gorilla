<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Contracts\Reports\MowerReportInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\MowerRequestReport;
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
