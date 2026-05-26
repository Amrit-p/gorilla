<?php

namespace App\Support;

use App\Models\User;

/**
 * Assigns sequential Gorilla CRM user IDs (default starting at 1001).
 */
final class UserUniqueIdGenerator
{
    public static function next(): int
    {
        $start = (int) config('mowing.user_unique_id_start', 1001);
        $currentMax = (int) User::withTrashed()->max('user_unique_id');

        return max($start, $currentMax + 1);
    }
}
