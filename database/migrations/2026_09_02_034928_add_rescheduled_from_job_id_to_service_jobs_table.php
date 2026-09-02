<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->foreignId('rescheduled_from_job_id')
                ->nullable()
                ->after('rescheduled_at')
                ->constrained('service_jobs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropForeign(['rescheduled_from_job_id']);
            $table->dropColumn('rescheduled_from_job_id');
        });
    }
};
