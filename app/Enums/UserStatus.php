<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';

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

    public static function fromActiveFlag(bool $isActive): self
    {
        return $isActive ? self::ACTIVE : self::INACTIVE;
    }
}
