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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('service_type', 40)->nullable()->after('address');
            $table->string('weed_spray', 10)->nullable()->after('service_type');
            $table->string('equipment_type', 40)->nullable()->after('weed_spray');
            $table->string('re_completion_days', 20)->nullable()->after('equipment_type');
            $table->string('job_type', 20)->nullable()->after('re_completion_days');
            $table->decimal('charges', 10, 2)->nullable()->after('job_type');
            $table->string('payment_mode', 20)->nullable()->after('charges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'service_type',
                'weed_spray',
                'equipment_type',
                're_completion_days',
                'job_type',
                'charges',
                'payment_mode',
            ]);
        });
    }
};
