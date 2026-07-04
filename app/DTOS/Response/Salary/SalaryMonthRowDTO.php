<?php

declare(strict_types=1);

namespace App\DTOS\Response\Salary;

use Illuminate\Support\Carbon;

class SalaryMonthRowDTO
{
    public function __construct(
        public int $mower_id,
        public string $mower_name,
        public int $year,
        public int $month,
        public string $month_label,
        public Carbon $period_start,
        public Carbon $period_end,
        public float $total_sales,
        public float $total_bonus,
        public float $percentage,
        public float $salary_amount,
        public ?int $receipt_id,
        public string $formula_description,
    ) {}

    public function toArray(): array
    {
        return [
            'mower_id' => $this->mower_id,
            'mower_name' => $this->mower_name,
            'year' => $this->year,
            'month' => $this->month,
            'month_label' => $this->month_label,
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'total_sales' => $this->total_sales,
            'total_bonus' => $this->total_bonus,
            'percentage' => $this->percentage,
            'salary_amount' => $this->salary_amount,
            'receipt_id' => $this->receipt_id,
            'has_receipt' => $this->receipt_id !== null,
            'formula_description' => $this->formula_description,
        ];
    }
}
