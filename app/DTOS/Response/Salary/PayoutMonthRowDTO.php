<?php

declare(strict_types=1);

namespace App\DTOS\Response\Salary;

class PayoutMonthRowDTO
{
    public function __construct(
        public int $mower_id,
        public string $mower_name,
        public int $year,
        public int $month,
        public string $month_label,
        public float $total_sales,
        public float $total_bonus,
        public float $total_payout,
        public float $total_hours,
        public int $payout_count,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mower_id' => $this->mower_id,
            'mower_name' => $this->mower_name,
            'year' => $this->year,
            'month' => $this->month,
            'month_label' => $this->month_label,
            'total_sales' => $this->total_sales,
            'total_bonus' => $this->total_bonus,
            'total_payout' => $this->total_payout,
            'total_hours' => $this->total_hours,
            'payout_count' => $this->payout_count,
        ];
    }
}
