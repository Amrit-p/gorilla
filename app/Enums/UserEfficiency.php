<?php

namespace App\Enums;

enum UserEfficiency: string
{
    case BEGINNER = 'Beginner';
    case AVERAGE = 'Average';
    case GOOD = 'Good';

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
