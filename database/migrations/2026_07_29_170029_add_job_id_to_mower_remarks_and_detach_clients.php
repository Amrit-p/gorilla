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
        if (! Schema::hasColumn('mower_remarks', 'job_id')) {
            return;
        }

        $this->dropForeignKeysFor('mower_remarks', 'job_id');

        Schema::table('mower_remarks', function (Blueprint $table): void {
            $table->dropColumn('job_id');
        });
    }

    /**
     * Drop the foreign key on a column. A historical rebuild of mower_remarks renamed
     * the table, so the constraint does not always follow Laravel's
     * {table}_{column}_foreign convention. SQLite only supports dropping by column.
     */
    private function dropForeignKeysFor(string $table, string $column): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table($table, function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
            });

            return;
        }

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (! in_array($column, $foreignKey['columns'], true)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($foreignKey): void {
                $blueprint->dropForeign($foreignKey['name']);
            });
        }
    }
};
