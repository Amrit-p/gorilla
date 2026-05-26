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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->string('address')->nullable();
            $table->text('property_details')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_sales_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('New')->index();
            $table->boolean('is_locked')->default(false)->index();
            $table->boolean('queued_for_scheduling')->default(false)->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
