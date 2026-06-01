<?php

declare(strict_types=1);

namespace App\DTOS\Response\Reports;

class MowerResponseReportDTO
{
    public function __construct(
        public string $name = "",
        public int $user_id = 0,
        public int $total_jobs_completed = 0,
        public int $total_jobs_started = 0,
        public int $total_jobs_pending = 0,
        public float $completed_earnings = 0.0,
        public float $started_earnings = 0.0,
        public float $pending_earnings = 0.0,
        public int $total_cash_earned = 0,
        public int $total_online_earned = 0,
        public float $total_sales = 0.0,
        public float $total_incentive_amount = 0.0,
        public float $total_working_hours = 0.0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? 'Unknown',
            user_id: $data['user_id'] ?? 0,
            total_jobs_completed: $data['total_jobs_completed'] ?? 0,
            total_jobs_started: $data['total_jobs_started'] ?? 0,
            total_jobs_pending: $data['total_jobs_pending'] ?? 0,
            completed_earnings: $data['completed_earnings'] ?? 0.0,
            started_earnings: $data['started_earnings'] ?? 0.0,
            pending_earnings: $data['pending_earnings'] ?? 0.0,
            total_cash_earned: $data['total_cash_earned'] ?? 0,
            total_online_earned: $data['total_online_earned'] ?? 0,
            total_sales: $data['total_sales'] ?? 0.0,
            total_incentive_amount: $data['total_incentive_amount'] ?? 0.0,
            total_working_hours: $data['total_working_hours'] ?? 0.0,
        );
    }

    public function toArray(): array
    {
        return [
            'name'    => $this->name,
            'user_id' => $this->user_id,
            'total_jobs_completed' => $this->total_jobs_completed,
            'total_jobs_started' => $this->total_jobs_started,
            'total_jobs_pending' => $this->total_jobs_pending,
            'completed_earnings' => $this->completed_earnings,
            'started_earnings' => $this->started_earnings,
            'pending_earnings' => $this->pending_earnings,
            'total_cash_earned' => $this->total_cash_earned,
            'total_online_earned' => $this->total_online_earned,
            'total_sales' => $this->total_sales,
            'total_incentive_amount' => $this->total_incentive_amount,
            'total_earnings' => $this->calculateTotalEarnings(),
            'total_working_hours' => $this->total_working_hours,
        ];
    }

    public function withTotalSales(float $totalSales): self
    {
        $this->total_sales = $totalSales;
        return $this;
    }

    public function withTotalCashEarned(int $totalCashEarned): self
    {
        $this->total_cash_earned = $totalCashEarned;
        return $this;
    }

    public function withTotalOnlineEarned(int $totalOnlineEarned): self
    {
        $this->total_online_earned = $totalOnlineEarned;
        return $this;
    }

    public function withTotalJobsCompleted(int $totalJobsCompleted): self
    {
        $this->total_jobs_completed = $totalJobsCompleted;
        return $this;
    }

    public function withTotalJobsStarted(int $totalJobsStarted): self
    {
        $this->total_jobs_started = $totalJobsStarted;
        return $this;
    }

    public function withTotalJobsPending(int $totalJobsPending): self
    {
        $this->total_jobs_pending = $totalJobsPending;
        return $this;
    }

    public function withCompletedEarnings(float $completedEarnings): self
    {
        $this->completed_earnings = $completedEarnings;
        return $this;
    }

    public function withStartedEarnings(float $startedEarnings): self
    {
        $this->started_earnings = $startedEarnings;
        return $this;
    }

    public function withPendingEarnings(float $pendingEarnings): self
    {
        $this->pending_earnings = $pendingEarnings;
        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function withUserId(int $userId): self
    {
        $this->user_id = $userId;
        return $this;
    }

    public function calculateTotalEarnings(): float
    {
        return $this->completed_earnings + $this->started_earnings + $this->pending_earnings;
    }

    public function withTotalIncentiveAmount(float $totalIncentiveAmount): self
    {
        $this->total_incentive_amount = $totalIncentiveAmount;
        return $this;
    }

    public function withTotalWorkingHours(float $totalWorkingHours): self
    {
        $this->total_working_hours = $totalWorkingHours;
        return $this;
    }
}
