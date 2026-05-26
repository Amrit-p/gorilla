<?php

namespace App\Support;

use App\Helpers\OptimizationHelper;
use App\Models\EquipmentType;
use App\Models\SafetyType;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * Cached reads for dynamic master catalog tables.
 */
final class MasterCatalog
{
    public const SERVICE_TYPES = 'service_types';

    public const EQUIPMENT_TYPES = 'equipment_types';

    public const SAFETY_TYPES = 'safety_types';

    /**
     * @return class-string<Model>
     */
    public static function modelClass(string $catalog): string
    {
        return match ($catalog) {
            self::SERVICE_TYPES => ServiceType::class,
            self::EQUIPMENT_TYPES => EquipmentType::class,
            self::SAFETY_TYPES => SafetyType::class,
            default => throw new \InvalidArgumentException("Unknown catalog: {$catalog}"),
        };
    }

    public static function forgetCache(string $catalog): void
    {
        Cache::forget(self::cacheKey($catalog, 'names'));
        Cache::forget(self::cacheKey($catalog, 'active_names'));
        Cache::forget(self::cacheKey($catalog, 'equipment_options'));

        if ($catalog === self::SERVICE_TYPES) {
            OptimizationHelper::forgetDashboardStats();
        }
    }

    /**
     * @return array<int, string>
     */
    public static function activeNames(string $catalog): array
    {
        return Cache::remember(
            self::cacheKey($catalog, 'active_names'),
            (int) config('mowing.cache.ttl.master_catalog_seconds', 300),
            function () use ($catalog): array {
                $model = self::modelClass($catalog);

                return $model::query()->active()->ordered()->pluck('name')->all();
            }
        );
    }

    /**
     * @return array<int, mixed>
     */
    public static function activeNameRules(string $catalog): array
    {
        return ['string', Rule::in(self::activeNames($catalog))];
    }

    /**
     * @return array<int, array{name: string, color_code?: string}>
     */
    public static function activeEquipmentOptions(): array
    {
        return Cache::remember(
            self::cacheKey(self::EQUIPMENT_TYPES, 'equipment_options'),
            (int) config('mowing.cache.ttl.master_catalog_seconds', 300),
            fn (): array => EquipmentType::query()
                ->active()
                ->ordered()
                ->get(['id', 'name', 'color_code'])
                ->map(fn (EquipmentType $row): array => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'color_code' => $row->color_code,
                ])
                ->all()
        );
    }

    /**
     * @return array<int, string>
     */
    public static function activeEquipmentNames(): array
    {
        return array_column(self::activeEquipmentOptions(), 'name');
    }

    private static function cacheKey(string $catalog, string $suffix): string
    {
        return "mowing.catalog.{$catalog}.{$suffix}_v1";
    }
}
