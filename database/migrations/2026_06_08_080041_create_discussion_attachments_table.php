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
        Schema::create('discussion_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_section_id')->constrained('discussion_sections')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->enum('file_type', ['image', 'pdf', 'document']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_attachments');
    }
};
