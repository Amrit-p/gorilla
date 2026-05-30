<?php

namespace App\Enums;

enum ClientPaymentStatus: string
{
    case PENDING = 'Pending';
    case DONE = 'Done';
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
