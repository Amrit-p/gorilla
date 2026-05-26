<?php

namespace App\Enums;

enum LeadEquipmentType: string
{
    case HONDA_CATCHER = 'Honda Catcher.';
    case YELLOW_MOWER = 'yellow mower';
    case RED_MOWER = 'Red mower';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases()
        );
    }
}
