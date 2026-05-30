<?php

namespace App\Enums;

enum JobOperationalPaymentStatus: string
{
    case RECEIVED = 'Received';
    case PENDING = 'Pending';
    case PARTIAL = 'Partial';

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
