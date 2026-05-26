<?php

namespace App\Enums;

enum LeadServiceType: string
{
    case MULCHING = 'Mulching';
    case SIDE_SHOOT = 'Side shoot';
    case CUT_AND_LEAVE = 'Cut and leave';
    case CUT_AND_AWAY = 'Cut and Away';

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
