<?php

declare(strict_types=1);

namespace App\DTOS\Response\Reports;

class ChecklistReportResponseDTO
{
    public function __construct(
        public int $user_id = 0,
        public string $name = '',
        public int $checklist_id = 0,
        public string $checklist_name = '',
        public int $total_submissions = 0,
        public int $days_submitted = 0,
        public array $submissions_by_date = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: $data['user_id'] ?? 0,
            name: $data['name'] ?? 'Unknown',
            checklist_id: $data['checklist_id'] ?? 0,
            checklist_name: $data['checklist_name'] ?? '',
            total_submissions: $data['total_submissions'] ?? 0,
            days_submitted: $data['days_submitted'] ?? 0,
            submissions_by_date: $data['submissions_by_date'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'checklist_id' => $this->checklist_id,
            'checklist_name' => $this->checklist_name,
            'total_submissions' => $this->total_submissions,
            'days_submitted' => $this->days_submitted,
            'submissions_by_date' => $this->submissions_by_date,
        ];
    }

    public function withUserId(int $userId): self
    {
        $this->user_id = $userId;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withChecklistId(int $checklistId): self
    {
        $this->checklist_id = $checklistId;

        return $this;
    }

    public function withChecklistName(string $checklistName): self
    {
        $this->checklist_name = $checklistName;

        return $this;
    }

    public function withTotalSubmissions(int $totalSubmissions): self
    {
        $this->total_submissions = $totalSubmissions;

        return $this;
    }

    public function withDaysSubmitted(int $daysSubmitted): self
    {
        $this->days_submitted = $daysSubmitted;

        return $this;
    }

    public function withSubmissionsByDate(array $submissionsByDate): self
    {
        $this->submissions_by_date = $submissionsByDate;

        return $this;
    }
}
