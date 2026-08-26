<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PayoutSelectionRequest;
use App\Http\Requests\Admin\SalaryMonthJobsRequest;
use App\Http\Requests\Admin\SalaryMonthlyTableRequest;
use App\Http\Requests\Admin\StoreMowerPayoutRequest;
use App\Models\MowerPayout;
use App\Models\User;
use App\Services\JobManagementService;
use App\Services\SalaryCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SalaryCalculatorController extends Controller
{
    public function __construct(
        private readonly SalaryCalculatorService $salaryCalculatorService,
        private readonly JobManagementService $jobManagementService
    ) {}

    public function index(): View
    {
        $this->authorize('manage-salary-calculator');

        // The drill-down renders the full jobs table, so this page needs the same
        // form options its row actions and modals rely on.
        return view('admin.salary.calculator.index', [
            'mowers' => $this->salaryCalculatorService->mowers(),
            'currentYear' => (int) now()->year,
            ...$this->jobManagementService->formOptions(),
        ]);
    }

    /** The aggregated month-by-month table for one mower and year. */
    public function months(SalaryMonthlyTableRequest $request): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $mower = User::findOrFail($request->validated('mower_id'));
        $rows = $this->salaryCalculatorService->monthlyBreakdown($mower, (int) $request->validated('year'));

        return response()->json([
            'html' => view('admin.salary.calculator.partials.months-table', [
                'rows' => $rows,
                'mower' => $mower,
            ])->render(),
        ]);
    }

    /** The drill-down sub-table of completed jobs for one mower and month. */
    public function jobs(SalaryMonthJobsRequest $request): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $mower = User::findOrFail($request->validated('mower_id'));
        $year = (int) $request->validated('year');
        $month = (int) $request->validated('month');

        $jobs = $this->salaryCalculatorService->jobsForMonth($mower, $year, $month);

        return response()->json([
            'html' => view('admin.salary.calculator.partials.jobs-table', [
                'jobs' => $jobs,
                'mower' => $mower,
                'year' => $year,
                'month' => $month,
            ])->render(),
            'month_label' => Carbon::create($year, $month, 1)->format('F Y'),
        ]);
    }

    /** The list of payouts counted against one mower and month, for the modal. */
    public function monthPayouts(SalaryMonthJobsRequest $request): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $mower = User::findOrFail($request->validated('mower_id'));
        $year = (int) $request->validated('year');
        $month = (int) $request->validated('month');

        $payouts = $this->salaryCalculatorService->payoutsForMonth($mower, $year, $month);

        return response()->json([
            'html' => view('admin.salary.calculator.partials.payouts-table', [
                'payouts' => $payouts,
                'mower' => $mower,
            ])->render(),
            'month_label' => Carbon::create($year, $month, 1)->format('F Y'),
            'count' => $payouts->count(),
        ]);
    }

    /** The jobs a single payout settles, for the drill-down inside the payouts modal. */
    public function payoutJobs(MowerPayout $mowerPayout): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $jobs = $mowerPayout->jobs()
            ->orderBy('service_jobs.scheduled_date')
            ->orderBy('service_jobs.id')
            ->get(['service_jobs.id', 'customer_name', 'scheduled_date', 'required_services', 'charges', 'consumed_time_minutes', 'status']);

        return response()->json([
            'html' => view('admin.salary.calculator.partials.payout-jobs-table', [
                'jobs' => $jobs,
                'payout' => $mowerPayout,
            ])->render(),
            'count' => $jobs->count(),
        ]);
    }

    /** Delete a payout, releasing its jobs so they can be paid out again. */
    public function destroyPayout(MowerPayout $mowerPayout): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $this->salaryCalculatorService->deletePayout($mowerPayout);

        return response()->json(['message' => 'Payout deleted.']);
    }

    /** Totals and validity for an arbitrary job selection, used by the payout modal. */
    public function selection(PayoutSelectionRequest $request): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        return response()->json(
            $this->salaryCalculatorService->payoutSelectionSummary($request->validated('job_ids'))
        );
    }

    /**
     * Record the payouts for the selected jobs - one per mower in the selection.
     * Generic jobs views have no mower filter, so a missing mower_id is resolved
     * from the jobs themselves.
     */
    public function store(StoreMowerPayoutRequest $request): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $result = $this->salaryCalculatorService->savePayouts(
            $request->validated('payouts'),
            $request->user()
        );

        $parts = [];

        if ($result['created'] > 0) {
            $parts[] = $result['created'].' payout'.($result['created'] === 1 ? '' : 's').' recorded';
        }

        if ($result['updated'] > 0) {
            $parts[] = $result['updated'].' payout'.($result['updated'] === 1 ? '' : 's').' updated';
        }

        return response()->json([
            'created' => $result['created'],
            'updated' => $result['updated'],
            'message' => ($parts === [] ? 'Nothing to save' : implode(', ', $parts)).'.',
        ]);
    }
}
