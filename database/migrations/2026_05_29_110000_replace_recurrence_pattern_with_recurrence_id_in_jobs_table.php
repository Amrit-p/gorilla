<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->foreignId('recurrence_id')->nullable()->after('is_recurring')->constrained('recurrences')->nullOnDelete();
            $table->dropColumn('recurrence_pattern');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['recurrence_id']);
            $table->dropColumn('recurrence_id');
            $table->string('recurrence_pattern', 50)->nullable()->after('is_recurring');
        });
    }
};
