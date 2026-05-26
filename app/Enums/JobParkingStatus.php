<?php

namespace App\Enums;

enum JobParkingStatus: string
{
    case EASY = 'Easy';
    case NOT_EASY = 'Not Easy';
    case LONG_AWAY = 'Long Away';

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
