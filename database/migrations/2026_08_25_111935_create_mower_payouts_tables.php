<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mower_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mower_payout_job', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mower_payout_id')->constrained('mower_payouts')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->timestamps();

            // A job can only ever belong to one payout, which is what stops double-payment.
            $table->unique('job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mower_payout_job');
        Schema::dropIfExists('mower_payouts');
    }
};
