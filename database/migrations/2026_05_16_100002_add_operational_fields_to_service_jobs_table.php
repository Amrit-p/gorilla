<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->string('client_address')->nullable()->after('lead_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('client_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable()->after('scheduled_time');
            $table->json('required_services')->nullable()->after('estimated_duration_minutes');
            $table->string('parking_status', 20)->nullable()->after('site_instructions');
            $table->string('customer_type', 20)->nullable()->after('parking_status');
            $table->text('pet_warning')->nullable()->after('customer_type');
            $table->json('attached_images')->nullable()->after('pet_warning');
            $table->foreignId('done_by_user_id')->nullable()->after('attached_images')->constrained('users')->nullOnDelete();
            $table->string('payment_mode', 20)->nullable()->after('done_by_user_id');
            $table->string('payment_status', 20)->nullable()->after('payment_mode');
            $table->string('payment_pending_reason', 255)->nullable()->after('payment_status');
            $table->text('special_remarks')->nullable()->after('payment_pending_reason');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('done_by_user_id');
            $table->dropColumn([
                'client_address',
                'latitude',
                'longitude',
                'estimated_duration_minutes',
                'required_services',
                'parking_status',
                'customer_type',
                'pet_warning',
                'attached_images',
                'payment_mode',
                'payment_status',
                'payment_pending_reason',
                'special_remarks',
            ]);
        });
    }
};
