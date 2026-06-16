<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->string('second_payment')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::statement("UPDATE service_jobs SET second_payment = NULL WHERE second_payment IS NOT NULL AND second_payment NOT REGEXP '^-?[0-9]+(\\.[0-9]+)?$'");

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->decimal('second_payment', 10, 2)->nullable()->change();
        });
    }
};
