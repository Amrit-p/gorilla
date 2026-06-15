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
        Schema::create('database_backups', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('disk', 20)->default('local');
            $table->string('path')->default('');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
