<?php

namespace App\Services;

use App\Models\Checklist;
use App\Models\ChecklistPoint;
use App\Models\User;
use App\Repositories\ChecklistRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChecklistService
{
    public function __construct(
        private readonly ChecklistRepository $repository,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function paginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginatedList($filters, $perPage);
    }

    public function create(User $actor, array $data): Checklist
    {
        $checklist = Checklist::create(['name' => trim($data['name'])]);

        $this->syncPoints($checklist, $data['points'] ?? []);

        $this->log($actor, 'checklist.created', 'Checklist created.', $checklist);

        return $checklist;
    }

    public function update(User $actor, Checklist $checklist, array $data): Checklist
    {
        $checklist->update(['name' => trim($data['name'])]);

        $existingPoints = $checklist->points->keyBy('id');
        $submittedIds   = collect($data['points'] ?? [])->pluck('id')->filter()->values()->all();

        // Archive points that were removed from the submission
        $existingPoints->reject(fn ($p) => in_array($p->id, $submittedIds))
            ->each(fn ($p) => $p->update(['is_archived' => true]));

        foreach ($data['points'] ?? [] as $pointData) {
            $submittedId = $pointData['id'] ?? null;

            if ($submittedId && $existingPoints->has($submittedId)) {
                $existing = $existingPoints->get($submittedId);

                if ($existing->heading !== trim($pointData['heading']) || $existing->text !== trim($pointData['text'])) {
                    // Content changed: archive old point and create a replacement
                    $existing->update(['is_archived' => true]);

                    ChecklistPoint::create([
                        'checklist_id'           => $checklist->id,
                        'heading'                => trim($pointData['heading']),
                        'text'                   => trim($pointData['text']),
                        'sort_order'             => (int) $pointData['sort_order'],
                        'archived_from_point_id' => $existing->id,
                    ]);
                } else {
                    // Only position may have changed
                    $existing->update(['sort_order' => (int) $pointData['sort_order']]);
                }
            } else {
                // Brand-new point
                ChecklistPoint::create([
                    'checklist_id' => $checklist->id,
                    'heading'      => trim($pointData['heading']),
                    'text'         => trim($pointData['text']),
                    'sort_order'   => (int) $pointData['sort_order'],
                ]);
            }
        }

        $this->log($actor, 'checklist.updated', 'Checklist updated.', $checklist);

        return $checklist;
    }

    public function findWithPoints(Checklist $checklist): Checklist
    {
        return $checklist->load('points');
    }

    /**
     * @param  array<int, array{heading: string, text: string, sort_order: int}>  $points
     */
    private function syncPoints(Checklist $checklist, array $points): void
    {
        foreach ($points as $pointData) {
            ChecklistPoint::create([
                'checklist_id' => $checklist->id,
                'heading'      => trim($pointData['heading']),
                'text'         => trim($pointData['text']),
                'sort_order'   => (int) $pointData['sort_order'],
            ]);
        }
    }

    private function log(User $actor, string $action, string $description, Checklist $checklist): void
    {
        $this->activityLogService->log($actor, $action, $description, [
            'checklist_id'   => $checklist->getKey(),
            'checklist_name' => $checklist->name,
        ]);
    }
}
