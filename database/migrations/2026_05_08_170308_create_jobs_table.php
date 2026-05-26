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
        // Use service_jobs table name to avoid collision with Laravel queue jobs table.
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->date('scheduled_date')->index();
            $table->time('scheduled_time')->nullable();
            $table->boolean('is_recurring')->default(false)->index();
            $table->string('recurrence_pattern', 50)->nullable();
            $table->unsignedInteger('route_sequence')->default(0)->index();
            $table->string('priority', 20)->default('Medium')->index();
            $table->string('status', 30)->default('Pending')->index();
            $table->text('site_instructions')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_jobs');
    }
};
