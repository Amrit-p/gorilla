<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Service type names from the master catalog (falls back to config for empty DB).
 */
class ServiceTypes
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        $fromDb = MasterCatalog::activeNames(MasterCatalog::SERVICE_TYPES);

        if ($fromDb !== []) {
            return $fromDb;
        }

        return array_values(config('mowing.service_types', []));
    }

    /**
     * @return array<int, mixed>
     */
    public static function itemRules(): array
    {
        return ['string', Rule::in(self::all())];
    }

    /**
     * @return array<int, string>
     */
    public static function parseCsvCell(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $allowed = self::all();
        $parts = array_map('trim', explode(',', $value));

        return array_values(array_filter(
            $parts,
            static fn (string $part): bool => in_array($part, $allowed, true)
        ));
    }
}
