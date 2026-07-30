<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mower_remarks', function (Blueprint $table): void {
            if (! Schema::hasColumn('mower_remarks', 'job_id')) {
                $table->foreignId('job_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('service_jobs')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('mower_remarks', 'client_id')) {
            // Backfill job_id from the latest service_job for each client when possible.
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('
                    UPDATE mower_remarks AS mr
                    INNER JOIN (
                        SELECT client_id, MAX(id) AS job_id
                        FROM service_jobs
                        WHERE client_id IS NOT NULL
                          AND deleted_at IS NULL
                        GROUP BY client_id
                    ) AS latest ON latest.client_id = mr.client_id
                    SET mr.job_id = latest.job_id
                    WHERE mr.job_id IS NULL
                ');
            } else {
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

            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->dropForeign(['client_id']);
            });

            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->unsignedBigInteger('client_id')->nullable()->change();
                $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mower_remarks', 'job_id')) {
            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('job_id');
            });
        }

        if (Schema::hasColumn('mower_remarks', 'client_id')) {
            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->dropForeign(['client_id']);
            });

            Schema::table('mower_remarks', function (Blueprint $table): void {
                $table->unsignedBigInteger('client_id')->nullable(false)->change();
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            });
        }
    }
};
