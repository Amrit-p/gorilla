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
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_notes', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
        });
    }
};
