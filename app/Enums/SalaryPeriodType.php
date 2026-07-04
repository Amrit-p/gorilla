<?php

namespace App\Enums;

enum SalaryPeriodType: string
{
    case WEEK = 'week';
    case MONTH = 'month';

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
