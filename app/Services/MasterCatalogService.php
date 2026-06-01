<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\MasterCatalogRepository;
use App\Support\MasterCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class MasterCatalogService
{
    public function __construct(
        private readonly MasterCatalogRepository $repository,
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function paginated(string $catalog, string $modelClass, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginatedList($modelClass, $filters, $perPage);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function create(User $actor, string $catalog, string $modelClass, array $data, string $logAction): Model
    {
        $record = $modelClass::query()->create($this->preparePayload($catalog, $data));

        MasterCatalog::forgetCache($catalog);
        $this->log($actor, $logAction, "{$catalog} master created.", $record);

        return $record;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public function update(User $actor, string $catalog, Model $record, array $data, string $logAction): Model
    {
        $record->fill($this->preparePayload($catalog, $data, $record));
        $record->save();

        MasterCatalog::forgetCache($catalog);
        $this->log($actor, $logAction, "{$catalog} master updated.", $record);

        return $record;
    }

    public function toggleStatus(User $actor, string $catalog, Model $record, bool $isActive, string $logAction): Model
    {
        $record->is_active = $isActive;
        $record->save();

        MasterCatalog::forgetCache($catalog);
        $this->log($actor, $logAction, "{$catalog} master status changed.", $record, ['is_active' => $isActive]);

        return $record;
    }

    public function delete(User $actor, string $catalog, Model $record, string $logAction): void
    {
        $record->delete();

        MasterCatalog::forgetCache($catalog);
        $this->log($actor, $logAction, "{$catalog} master deleted.", $record);
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(string $catalog, array $data, ?Model $existing = null): array
    {
        $payload = [
            'name' => trim((string) $data['name']),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? ($existing?->sort_order ?? 0)),
        ];

        if ($catalog === MasterCatalog::EQUIPMENT_TYPES) {
            $payload['color_code'] = $this->normalizeColorCode((string) ($data['color_code'] ?? '#64748b'));
        }

        if ($catalog === MasterCatalog::ACCOUNTING_LEVELS) {
            $payload['description'] = trim((string) ($data['description'] ?? ''));
        }

        return $payload;
    }

    private function normalizeColorCode(string $color): string
    {
        $color = trim($color);
        if ($color === '') {
            return '#64748b';
        }

        if (! str_starts_with($color, '#')) {
            $color = '#'.$color;
        }

        return strtolower($color);
    }

    private function log(User $actor, string $action, string $description, Model $record, array $context = []): void
    {
        $this->activityLogService->log($actor, $action, $description, array_merge([
            'master_id' => $record->getKey(),
            'master_name' => $record->getAttribute('name'),
        ], $context));
    }
}
