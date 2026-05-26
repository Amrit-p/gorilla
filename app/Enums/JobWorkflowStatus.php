<?php

namespace App\Enums;

enum JobWorkflowStatus: string
{
    case STARTED = 'Started';
    case HOLD = 'Hold';
    case COMPLETED = 'Completed';

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

    public function badgeType(): string
    {
        return match ($this) {
            self::STARTED => 'info',
            self::HOLD => 'warning',
            self::COMPLETED => 'success',
        };
    }

    public static function badgeTypeFor(string $status): string
    {
        return self::tryFrom($status)?->badgeType() ?? 'default';
    }
}
