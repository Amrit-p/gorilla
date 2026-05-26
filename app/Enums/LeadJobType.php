<?php

namespace App\Enums;

enum LeadJobType: string
{
    case REGULAR = 'Regular';
    case ON_CALL = 'On call';
    case NEW = 'New';

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
