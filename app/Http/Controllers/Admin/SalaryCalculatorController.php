<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateSalaryReceiptRequest;
use App\Http\Requests\Admin\SalaryMonthlyTableRequest;
use App\Models\User;
use App\Services\SalaryCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SalaryCalculatorController extends Controller
{
    public function __construct(
        private readonly SalaryCalculatorService $salaryCalculatorService
    ) {}

    public function index(): View
    {
        $this->authorize('manage-salary-calculator');

        return view('admin.salary.calculator.index', [
            'mowers' => $this->salaryCalculatorService->mowers(),
            'currentYear' => (int) now()->year,
        ]);
    }

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

    public function store(GenerateSalaryReceiptRequest $request): JsonResponse
    {
        $this->authorize('manage-salary-calculator');

        $mower = User::findOrFail($request->validated('mower_id'));

        $receipt = $this->salaryCalculatorService->generate(
            $mower,
            (int) $request->validated('year'),
            (int) $request->validated('month'),
            (float) $request->validated('percentage'),
            $request->user()
        );

        return response()->json([
            'receipt_id' => $receipt->id,
            'receipt_url' => route('admin.salary-receipts.show', $receipt),
            'salary_amount' => (float) $receipt->salary_amount,
        ]);
    }
}
