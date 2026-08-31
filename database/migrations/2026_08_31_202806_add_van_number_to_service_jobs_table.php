<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->string('van_number', 50)->nullable()->after('equipment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropColumn('van_number');
        });
    }
};
