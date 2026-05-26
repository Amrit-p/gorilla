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
        Schema::create('job_user_assignments', function (Blueprint $table) {
            $table->id();
            // FK added in follow-up migration to avoid same-timestamp ordering issues.
            $table->foreignId('job_id')->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('assignment_date')->nullable()->index();
            $table->string('assignment_status', 30)->default('Assigned')->index();
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_user_assignments');
    }
};
