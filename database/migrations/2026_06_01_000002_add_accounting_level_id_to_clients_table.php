<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('accounting_level_id')->nullable()->constrained('accounting_levels')->nullOnDelete()->after('zone_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['accounting_level_id']);
            $table->dropColumn('accounting_level_id');
        });
    }
};
