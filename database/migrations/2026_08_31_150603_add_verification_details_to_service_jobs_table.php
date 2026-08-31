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
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->date('verified_date')->nullable()->after('verified_by');
            $table->decimal('verified_amount', 10, 2)->nullable()->after('verified_date');
            $table->text('verification_comment')->nullable()->after('verified_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropColumn(['verified_date', 'verified_amount', 'verification_comment']);
        });
    }
};
