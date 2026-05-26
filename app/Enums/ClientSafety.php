<?php

namespace App\Enums;

enum ClientSafety: string
{
    case PET = 'Pet';
    case PUBLIC = 'Public';
    case VEHICLES = 'Vehicles';
    case STORMS = 'Storms';
    case STONES = 'Stones';
    case ANY_OTHER = 'Any Other';

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
