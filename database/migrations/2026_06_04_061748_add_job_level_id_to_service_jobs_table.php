<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->foreignId('job_level_id')->nullable()->constrained('job_levels')->nullOnDelete()->after('equipment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\JobLevel::class);
        });
    }
};
