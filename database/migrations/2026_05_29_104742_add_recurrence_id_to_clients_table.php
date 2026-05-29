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
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('recurrence_id')->nullable()->after('equipment_type_id')->constrained('recurrences')->nullOnDelete();
            $table->dropColumn('re_completion_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['recurrence_id']);
            $table->dropColumn('recurrence_id');
            $table->integer('re_completion_days')->nullable()->after('job_type');
        });
    }
};
