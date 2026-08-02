<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add job_id to mower_remarks and backfill from service_jobs.
 * client_id is fully removed in a later migration that drops the clients table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mower_remarks', 'job_id')) {
            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->foreignId('job_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('service_jobs')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mower_remarks', 'client_id')) {
            return;
        }

        $remarks = DB::table('mower_remarks')->whereNull('job_id')->whereNotNull('client_id')->get(['id', 'client_id']);
        foreach ($remarks as $remark) {
            $jobId = DB::table('service_jobs')
                ->where('client_id', $remark->client_id)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->value('id');

            if ($jobId) {
                DB::table('mower_remarks')->where('id', $remark->id)->update(['job_id' => $jobId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mower_remarks', 'job_id')) {
            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('job_id');
            });
        }
    }
};
