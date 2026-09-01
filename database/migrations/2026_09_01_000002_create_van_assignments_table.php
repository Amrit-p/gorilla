<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained('vans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->timestamps();

            // One mower per van per day, and one van per mower per day.
            $table->unique(['van_id', 'date']);
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_assignments');
    }
};
