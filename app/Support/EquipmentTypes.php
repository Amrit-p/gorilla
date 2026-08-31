<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class EquipmentTypes
{
    /**
     * Equipment a mower may pick when raising a job from the field.
     *
     * @var array<int, string>
     */
    public const MOWER_SELECTABLE = ['Red Mower', 'Yellow Mower'];

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
     * Active equipment restricted to what a mower may pick in the field.
     *
     * @return array<int, array{id: int, name: string, color_code: string}>
     */
    public static function mowerSelectOptions(): array
    {
        return array_values(array_filter(
            self::options(),
            static fn (array $option): bool => in_array($option['name'], self::MOWER_SELECTABLE, true)
        ));
    }

    /**
     * @return array<int, mixed>
     */
    public static function mowerIdRules(): array
    {
        return [
            'required',
            'integer',
            Rule::in(array_column(self::mowerSelectOptions(), 'id')),
        ];
    }

    /**
     * @return array<int, array{name: string, color_code: string}>
     */
    public static function options(): array
    {
        return MasterCatalog::activeEquipmentOptions();
    }
}
