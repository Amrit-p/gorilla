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
            if (!Schema::hasColumn('service_jobs', 'charges')) {
                $table->decimal('charges', 10, 2)->nullable()->after('payment_pending_reason');
            }
            if (!Schema::hasColumn('service_jobs', 'incentive_percentage')) {
                $table->decimal('incentive_percentage', 5, 2)->nullable()->after('charges');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropColumn(['charges', 'incentive_percentage']);
        });
    }
};
