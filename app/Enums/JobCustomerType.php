<?php

namespace App\Enums;

enum JobCustomerType: string
{
    case EASY = 'Easy';
    case TOUF = 'touf';
    case DONT_KNOW = "Don't know";

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
