<?php

namespace App\Support;

use App\Models\Client;

/**
 * Assigns sequential Gorilla CRM customer IDs (default starting at 2001).
 */
final class CustomerUniqueIdGenerator
{
    public static function next(): int
    {
        $start = (int) config('mowing.customer_unique_id_start', 2001);
        $currentMax = (int) Client::withTrashed()->max('customer_unique_id');

        return max($start, $currentMax + 1);
    }
}
