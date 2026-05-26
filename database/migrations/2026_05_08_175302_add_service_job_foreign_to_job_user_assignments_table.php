<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_user_assignments', function (Blueprint $table) {
            $table->foreign('job_id')
                ->references('id')
                ->on('service_jobs')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_user_assignments', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });
    }
};
