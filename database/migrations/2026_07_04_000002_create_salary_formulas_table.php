<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_formulas', function (Blueprint $table) {
            $table->id();
            $table->json('formula');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_formulas');
    }
};
