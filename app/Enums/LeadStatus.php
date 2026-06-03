<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'New';
    case FOLLOW_UP = 'Follow Up';
    case MATURE = 'Mature';
    case WON = 'Won';
    case LOST = 'Lost';

    private const EXCLUDED_FROM_FORM = [self::MATURE];

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

    /**
     * @return array<int, string>
     */
    public static function selectableValues(): array
    {
        return array_values(array_map(
            static fn (self $case): string => $case->value,
            array_filter(self::cases(), fn (self $case) => ! in_array($case, self::EXCLUDED_FROM_FORM, true))
        ));
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
