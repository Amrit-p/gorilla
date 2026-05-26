<?php

namespace App\Enums;

enum JobOperationalPaymentMode: string
{
    case ONLINE = 'Online';
    case CASH = 'Cash';
    case BOTH = 'Both';

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
