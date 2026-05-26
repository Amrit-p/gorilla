<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->unsignedSmallInteger('consumed_time_minutes')->nullable()->after('estimated_duration_minutes');
            $table->json('before_images')->nullable()->after('attached_images');
            $table->json('after_images')->nullable()->after('before_images');
        });
    }

    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropColumn(['consumed_time_minutes', 'before_images', 'after_images']);
        });
    }
};
