<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mower remarks now live directly on the job (a single current value) instead of
 * an append-only history table, matching how office manager remarks already work
 * via special_remarks.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_jobs', 'mower_remarks')) {
            Schema::table('service_jobs', function (Blueprint $table): void {
                $table->text('mower_remarks')->nullable()->after('special_remarks');
            });
        }

        $this->backfillMowerRemarksFromHistory();

        Schema::dropIfExists('mower_remarks');
    }

    public function down(): void
    {
        Schema::create('mower_remarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('service_jobs')->nullOnDelete();
            $table->text('description');
            $table->timestamps();
        });

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropColumn('mower_remarks');
        });
    }

    private function backfillMowerRemarksFromHistory(): void
    {
        if (! Schema::hasTable('mower_remarks')) {
            return;
        }

        $latestPerJob = DB::table('mower_remarks')
            ->whereNotNull('job_id')
            ->orderByDesc('id')
            ->get(['job_id', 'description'])
            ->unique('job_id');

        foreach ($latestPerJob as $remark) {
            DB::table('service_jobs')->where('id', $remark->job_id)->update([
                'mower_remarks' => $remark->description,
            ]);
        }
    }
};
