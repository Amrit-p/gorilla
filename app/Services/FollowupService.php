<?php

namespace App\Services;

use App\Enums\FollowupStatus;
use App\Models\Contractor;
use App\Models\Followup;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Repositories\FollowupRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FollowupService
{
    /** @var array<int, class-string> */
    public const ALLOWED_FOLLOWABLE_TYPES = [
        Job::class,
        Lead::class,
        Contractor::class,
    ];

    public function __construct(
        private readonly FollowupRepository $repository,
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * @param  array{search?: string, status?: string, followable_type?: string, assigned_to?: string, next_followup_from?: string, next_followup_to?: string}  $filters
     */
    public function paginatedFollowups(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->paginatedList($filters, $perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function followupsForFollowable(string $type, int $id): array
    {
        return $this->repository->forFollowable($type, $id)
            ->map(fn (Followup $f) => [
                'id' => $f->id,
                'title' => $f->title,
                'outcome' => $f->outcome,
                'notes' => $f->notes,
                'assigned_to' => $f->assigned_to,
                'assigned_to_name' => $f->assignedTo?->name,
                'status' => $f->status->value,
                'status_label' => $f->status->label(),
                'next_followup_at' => $f->next_followup_at?->format('d M Y H:i'),
                'next_followup_at_input' => $f->next_followup_at?->format('Y-m-d'),
                'created_by' => $f->createdBy?->name,
                'created_at' => $f->created_at->format('d M Y H:i'),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFollowup(User $actor, array $data): Followup
    {
        $followableType = $data['followable_type'] ?? null;
        $followableId = $followableType !== null ? ($data['followable_id'] ?? null) : null;

        // Only follow-ups pinned to a record supersede each other; general ones stand alone.
        if ($followableType !== null && $followableId !== null) {
            Followup::query()
                ->where('followable_type', $followableType)
                ->where('followable_id', $followableId)
                ->where('status', FollowupStatus::Pending->value)
                ->update(['status' => FollowupStatus::Completed->value]);
        }

        $followup = Followup::query()->create([
            'followable_type' => $followableType,
            'followable_id' => $followableId,
            'title' => $followableType === null ? trim((string) ($data['title'] ?? '')) : null,
            'created_by' => $actor->id,
            'assigned_to' => $data['assigned_to'] ?? null,
            'outcome' => $this->cleanOptionalText($data['outcome'] ?? null),
            'notes' => trim((string) ($data['notes'] ?? '')),
            'status' => $data['status'] ?? FollowupStatus::Pending->value,
            'next_followup_at' => $data['next_followup_at'] ?? null,
        ]);

        $this->activityLogService->log($actor, 'followup.created', 'Follow-up created.', [
            'followup_id' => $followup->id,
            'followable_type' => $followup->followable_type,
            'followable_id' => $followup->followable_id,
            'assigned_to' => $followup->assigned_to,
        ]);

        return $followup;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateFollowup(User $actor, Followup $followup, array $data): Followup
    {
        $attributes = [
            'assigned_to' => $data['assigned_to'] ?? null,
            'outcome' => $this->cleanOptionalText($data['outcome'] ?? null),
            'notes' => trim((string) ($data['notes'] ?? '')),
            'status' => $data['status'],
            'next_followup_at' => $data['next_followup_at'] ?? null,
        ];

        if ($followup->isGeneral()) {
            $attributes['title'] = trim((string) ($data['title'] ?? ''));
        }

        $followup->update($attributes);

        $this->activityLogService->log($actor, 'followup.updated', 'Follow-up updated.', [
            'followup_id' => $followup->id,
            'status' => $data['status'],
            'assigned_to' => $followup->assigned_to,
        ]);

        return $followup;
    }

    private function cleanOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function deleteFollowup(User $actor, Followup $followup): void
    {
        $this->activityLogService->log($actor, 'followup.deleted', 'Follow-up deleted.', [
            'followup_id' => $followup->id,
            'followable_type' => $followup->followable_type,
            'followable_id' => $followup->followable_id,
        ]);

        $followup->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return [
            'statuses' => FollowupStatus::cases(),
            'followableTypes' => self::ALLOWED_FOLLOWABLE_TYPES,
            'assignableUsers' => $this->assignableUsers(),
        ];
    }

    /**
     * Active users a follow-up can be assigned to.
     *
     * @return Collection<int, User>
     */
    public function assignableUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    public function searchFollowable(string $type, string $query): array
    {
        return match ($type) {
            Job::class => $this->searchJobs($query),
            Lead::class => $this->searchLeads($query),
            Contractor::class => $this->searchContractors($query),
            default => [],
        };
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function searchJobs(string $query): array
    {
        return Job::query()
            ->when($query !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('id', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->orWhere('client_address', 'like', "%{$query}%")
            ))
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn (Job $job) => [
                'id' => $job->id,
                'label' => "#{$job->id}"
                    .' – '.$job->customerDisplayName()
                    .($job->client_address ? " ({$job->client_address})" : ''),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function searchLeads(string $query): array
    {
        return Lead::query()
            ->when($query !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('id', 'like', "%{$query}%")
                ->orWhere('client_name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('mobile_number', 'like', "%{$query}%")
            ))
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'label' => "#{$lead->id}"
                    .($lead->client_name ? " – {$lead->client_name}" : '')
                    .($lead->email ? " ({$lead->email})" : ''),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function searchContractors(string $query): array
    {
        return Contractor::query()
            ->when($query !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('id', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
            ))
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn (Contractor $contractor) => [
                'id' => $contractor->id,
                'label' => "#{$contractor->id}"
                    .($contractor->name ? " – {$contractor->name}" : '')
                    .($contractor->email ? " ({$contractor->email})" : ''),
            ])
            ->all();
    }
}
