<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class SafetyTypes
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return MasterCatalog::activeNames(MasterCatalog::SAFETY_TYPES);
    }

    /**
     * @return array<int, mixed>
     */
    public static function itemRules(): array
    {
        return ['string', Rule::in(self::all())];
    }
}
