<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class EquipmentTypes
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return MasterCatalog::activeEquipmentNames();
    }

    /**
     * @return array<int, mixed>
     */
    public static function nameRules(): array
    {
        return ['required', 'string', Rule::in(self::all())];
    }

    /**
     * @return array<int, mixed>
     */
    public static function idRules(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('equipment_types', 'id')->where('is_active', true),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, color_code: string}>
     */
    public static function selectOptions(): array
    {
        return self::options();
    }

    /**
     * @return array<int, array{name: string, color_code: string}>
     */
    public static function options(): array
    {
        return MasterCatalog::activeEquipmentOptions();
    }
}
