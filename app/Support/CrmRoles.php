<?php

namespace App\Support;

/**
 * Canonical Gorilla CRM role labels (Spatie role "name" column).
 */
final class CrmRoles
{
    public const OFFICE_MANAGER = 'Office Manager';

    public const SALES_MANAGER = 'Sales Manager';

    public const MOWER = 'Mower';

    /**
     * Set to true to re-enable the Bonus column for Mower-role users everywhere
     * (table view, PDF export, Excel export). false = hidden for Mowers.
     */
    public const MOWER_SHOW_BONUS = false;

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(array_map(
            static fn (string $key): string => (string) config("permissions.roles.{$key}.label"),
            array_keys(config('permissions.roles', []))
        ));
    }

    public static function label(string $roleKey): string
    {
        return (string) config("permissions.roles.{$roleKey}.label");
    }
}
