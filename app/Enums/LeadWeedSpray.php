<?php

namespace App\Enums;

enum LeadWeedSpray: string
{
    case YES = 'Yes';
    case NO = 'No';
    case MAYBE = 'Maybe';

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
