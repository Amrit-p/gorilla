<?php

namespace App\Enums;

enum LeadReCompletionDays: string
{
    case DAYS_14 = '14 days';
    case DAYS_21 = '21 days';
    case DAYS_28 = '28 days';
    case DAYS_42 = '42 Days';

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
