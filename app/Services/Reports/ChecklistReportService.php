<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Contracts\Reports\ChecklistReportInterface;
use App\DTOS\Request\Reports\ChecklistReportRequestDTO;
use App\DTOS\Response\Reports\ChecklistReportResponseDTO;
use App\Models\MowerChecklistSubmission;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ChecklistReportService implements ChecklistReportInterface
{
    public function __construct(
        private readonly ChecklistReportExcelService $excelService
    ) {}

    public function generate(ChecklistReportRequestDTO $request): Collection
    {
        $submissions = MowerChecklistSubmission::query()
            ->with(['user', 'checklistPoint' => fn ($q) => $q->withoutGlobalScope('not_archived')->with('checklist')])
            ->when($request->user_id !== '', fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->start_date, fn ($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('date', '<=', $request->end_date))
            ->when($request->checklist_id, fn ($q) => $q->whereHas(
                'checklistPoint',
                fn ($pq) => $pq->withoutGlobalScope('not_archived')->where('checklist_id', $request->checklist_id)
            ))
            ->get();

        return $submissions
            ->groupBy('user_id')
            ->flatMap(function ($userSubmissions, $userId) {
                $user = $userSubmissions->first()->user;

                return $userSubmissions
                    ->groupBy(fn ($s) => $s->checklistPoint?->checklist_id ?? 0)
                    ->map(function ($checklistSubmissions, $checklistId) use ($userId, $user) {
                        $firstPoint = $checklistSubmissions->first()->checklistPoint;
                        $checklistName = $firstPoint?->checklist?->name ?? 'Unknown';

                        $submissionsByDate = $checklistSubmissions
                            ->groupBy(fn ($s) => $s->date->toDateString())
                            ->map(fn ($daySubmissions) => true)
                            ->toArray();

                        return (new ChecklistReportResponseDTO)
                            ->withUserId((int) $userId)
                            ->withName($user?->name ?? 'Unknown')
                            ->withChecklistId((int) $checklistId)
                            ->withChecklistName($checklistName)
                            ->withTotalSubmissions($checklistSubmissions->count())
                            ->withDaysSubmitted(count($submissionsByDate))
                            ->withSubmissionsByDate($submissionsByDate)
                            ->toArray();
                    });
            })
            ->sortBy([['name', 'asc'], ['checklist_name', 'asc']])
            ->values();
    }

    public function export(ChecklistReportRequestDTO $request): Response
    {
        return $this->excelService->export($this->generate($request), $request->start_date, $request->end_date);
    }
}
