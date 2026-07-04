<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Reports\MowerReportInterface;
use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\DTOS\Response\Reports\MowerResponseReportDTO;
use App\DTOS\Response\Salary\SalaryMonthRowDTO;
use App\Enums\SalaryPeriodType;
use App\Models\SalaryFormula;
use App\Models\SalaryReceipt;
use App\Models\User;
use App\Notifications\SalaryReceiptGeneratedNotification;
use App\Support\CrmRoles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use JWadhams\JsonLogic;

class SalaryCalculatorService
{
    public const DEFAULT_PERCENTAGE = 20.0;

    public function __construct(
        private readonly MowerReportInterface $mowerReport
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function defaultFormulaRule(): array
    {
        return [
            '+' => [
                ['*' => [['var' => 'percentage_fraction'], ['var' => 'total_sales']]],
                ['var' => 'total_bonus'],
            ],
        ];
    }

    public function formula(): SalaryFormula
    {
        return SalaryFormula::query()->first()
            ?? SalaryFormula::create(['formula' => self::defaultFormulaRule()]);
    }

    public function calculateAmount(float $totalSales, float $totalBonus, float $percentage): float
    {
        $amount = JsonLogic::apply($this->formula()->formula, [
            'total_sales' => $totalSales,
            'total_bonus' => $totalBonus,
            'percentage_fraction' => $percentage / 100,
        ]);

        return round((float) $amount, 2);
    }

    /** @return Collection<int, User> */
    public function mowers(): Collection
    {
        return User::role(CrmRoles::MOWER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'user_unique_id']);
    }

    /**
     * @return array<int, SalaryMonthRowDTO> one row per calendar month of the given year
     */
    public function monthlyBreakdown(User $mower, int $year): array
    {
        $existingReceipts = SalaryReceipt::query()
            ->where('mower_id', $mower->id)
            ->whereYear('period_start', $year)
            ->get()
            ->keyBy(fn (SalaryReceipt $receipt) => $receipt->period_start->month);

        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $receipt = $existingReceipts->get($month);

            if ($receipt) {
                $rows[] = new SalaryMonthRowDTO(
                    mower_id: $mower->id,
                    mower_name: $mower->name,
                    year: $year,
                    month: $month,
                    month_label: $start->format('F'),
                    period_start: $start,
                    period_end: $end,
                    total_sales: (float) $receipt->total_sales,
                    total_bonus: (float) $receipt->total_bonus,
                    percentage: (float) $receipt->percentage_used,
                    salary_amount: (float) $receipt->salary_amount,
                    receipt_id: $receipt->id,
                    formula_description: $receipt->formulaDescription(),
                );

                continue;
            }

            [$totalSales, $totalBonus] = $this->fetchTotals($mower, $start, $end);
            $salaryAmount = $this->calculateAmount($totalSales, $totalBonus, self::DEFAULT_PERCENTAGE);

            $rows[] = new SalaryMonthRowDTO(
                mower_id: $mower->id,
                mower_name: $mower->name,
                year: $year,
                month: $month,
                month_label: $start->format('F'),
                period_start: $start,
                period_end: $end,
                total_sales: $totalSales,
                total_bonus: $totalBonus,
                percentage: self::DEFAULT_PERCENTAGE,
                salary_amount: $salaryAmount,
                receipt_id: null,
                formula_description: SalaryFormula::describeCalculation(
                    $this->formula()->formula,
                    self::DEFAULT_PERCENTAGE,
                    $totalSales,
                    $totalBonus,
                    $salaryAmount
                ),
            );
        }

        return $rows;
    }

    public function generate(User $mower, int $year, int $month, float $percentage, User $admin): SalaryReceipt
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $this->assertNoExistingReceipt($mower, $start, $end);

        [$totalSales, $totalBonus] = $this->fetchTotals($mower, $start, $end);
        $formula = $this->formula();
        $salaryAmount = $this->calculateAmount($totalSales, $totalBonus, $percentage);

        $receipt = SalaryReceipt::create([
            'mower_id' => $mower->id,
            'period_type' => SalaryPeriodType::MONTH,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'total_sales' => $totalSales,
            'total_bonus' => $totalBonus,
            'percentage_used' => $percentage,
            'formula_snapshot' => $formula->formula,
            'salary_amount' => $salaryAmount,
            'generated_by' => $admin->id,
        ]);

        $mower->notify(new SalaryReceiptGeneratedNotification($receipt));

        $receipt->forceFill(['notified_at' => now()])->save();

        return $receipt;
    }

    public function deleteReceipt(SalaryReceipt $receipt): void
    {
        $receipt->delete();
    }

    /**
     * @return array{0: float, 1: float} [total_sales, total_bonus]
     */
    private function fetchTotals(User $mower, Carbon $start, Carbon $end): array
    {
        $requestDto = (new MowerRequestReportDTO)
            ->withUserId($mower->id)
            ->withStartDate($start)
            ->withEndDate($end);

        // JobListFilter matches jobs where the mower is either done_by_user_id or
        // merely an assigned collaborator, so the report can return a row for a
        // *different* user (the job's actual done_by_user_id). Only ever use the
        // row that belongs to the requested mower, never just the first result.
        $report = $this->mowerReport->generate($requestDto)->first(function ($row) use ($mower) {
            $rowData = $row instanceof MowerResponseReportDTO ? $row->toArray() : (array) $row;

            return (int) ($rowData['user_id'] ?? 0) === $mower->id;
        });

        $reportData = $report instanceof MowerResponseReportDTO ? $report->toArray() : (array) ($report ?? []);

        return [
            (float) ($reportData['total_sales'] ?? 0),
            (float) ($reportData['bonus'] ?? 0),
        ];
    }

    private function assertNoExistingReceipt(User $mower, Carbon $start, Carbon $end): void
    {
        $exists = SalaryReceipt::query()
            ->where('mower_id', $mower->id)
            ->whereDate('period_start', $start->toDateString())
            ->whereDate('period_end', $end->toDateString())
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'month' => 'A salary receipt already exists for this month. Delete it first to regenerate.',
            ]);
        }
    }
}
