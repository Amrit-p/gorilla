<?php

declare(strict_types=1);

namespace App\DTOS\Request\Reports;

use Illuminate\Support\Carbon;

class ChecklistReportRequestDTO
{
    public function __construct(
        public string $user_id = '',
        public ?Carbon $start_date = null,
        public ?Carbon $end_date = null,
        public ?int $checklist_id = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: $data['user_id'] ?? '',
            start_date: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            end_date: isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            checklist_id: isset($data['checklist_id']) ? (int) $data['checklist_id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'checklist_id' => $this->checklist_id,
        ];
    }

    public function withUserId(string $userId): self
    {
        $this->user_id = $userId;

        return $this;
    }

    public function withStartDate(Carbon $startDate): self
    {
        $this->start_date = $startDate;

        return $this;
    }

    public function withEndDate(Carbon $endDate): self
    {
        $this->end_date = $endDate;

        return $this;
    }

    public function withChecklistId(int $checklistId): self
    {
        $this->checklist_id = $checklistId;

        return $this;
    }
}
