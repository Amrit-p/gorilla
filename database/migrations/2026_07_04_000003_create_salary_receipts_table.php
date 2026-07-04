<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mower_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_type');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_sales', 12, 2);
            $table->decimal('total_bonus', 12, 2);
            $table->decimal('percentage_used', 5, 2);
            $table->json('formula_snapshot');
            $table->decimal('salary_amount', 12, 2);
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['mower_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_receipts');
    }
};
