<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('archived_from_checklist_id')->nullable();
            $table->timestamps();

            $table->foreign('archived_from_checklist_id')
                ->references('id')
                ->on('checklists')
                ->nullOnDelete();

            // Unique name among live (non-archived) checklists enforced in application layer
            $table->index(['is_archived', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};
