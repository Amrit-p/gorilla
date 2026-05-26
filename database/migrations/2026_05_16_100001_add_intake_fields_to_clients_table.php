<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('service_type', 40)->nullable()->after('address');
            $table->string('weed_spray', 10)->nullable()->after('service_type');
            $table->string('re_completion_days', 20)->nullable()->after('weed_spray');
            $table->string('job_type', 20)->nullable()->after('re_completion_days');
            $table->string('safety', 30)->nullable()->after('job_type');
            $table->string('safety_other', 120)->nullable()->after('safety');
            $table->decimal('charges', 10, 2)->nullable()->after('safety_other');
            $table->string('payment_mode', 20)->nullable()->after('charges');
            $table->string('remarks_type', 120)->nullable()->after('payment_mode');
            $table->string('payment_status', 20)->nullable()->after('remarks_type');
            $table->string('payment_status_reason', 255)->nullable()->after('payment_status');
            $table->decimal('latitude', 10, 7)->nullable()->after('payment_status_reason');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'service_type',
                'weed_spray',
                're_completion_days',
                'job_type',
                'safety',
                'safety_other',
                'charges',
                'payment_mode',
                'remarks_type',
                'payment_status',
                'payment_status_reason',
                'latitude',
                'longitude',
            ]);
        });
    }
};
