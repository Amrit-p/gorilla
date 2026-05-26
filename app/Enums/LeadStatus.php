<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'New';
    case FOLLOW_UP = 'Follow Up';
    case MATURE = 'Mature';
    case WON = 'Won';
    case LOST = 'Lost';

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

    public function convertsToClient(): bool
    {
        return in_array($this, [self::MATURE, self::WON], true);
    }

    public static function convertsToClientValue(string $status): bool
    {
        $enum = self::tryFrom($status);

        return $enum?->convertsToClient() ?? false;
    }
}
