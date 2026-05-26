<?php

namespace App\Enums;

enum ClientCustomerType: string
{
    case EASY = 'Easy';
    case HARD = 'Hard';
    case DONT_KNOW = "Don't Know";

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
