<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Correlated subquery that resolves the payout covering a job row. Selected as
 * `mower_payout_id`, it answers both "has this mower been paid for the job" and
 * "which payout should the admin edit" in a single pass.
 */
class MowerPayoutLookup
{
    public static function forJob(string $jobTable = 'service_jobs'): Builder
    {
        return DB::table('mower_payout_job')
            ->select('mower_payout_id')
            ->whereColumn('mower_payout_job.job_id', $jobTable.'.id')
            ->limit(1);
    }
}
