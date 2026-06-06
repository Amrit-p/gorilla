<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table): void {
            $table->dropForeign(['archived_from_checklist_id']);
            $table->dropIndex('checklists_is_archived_name_index');
            $table->dropColumn(['is_archived', 'archived_from_checklist_id']);
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table): void {
            $table->boolean('is_archived')->default(false)->after('name');
            $table->unsignedBigInteger('archived_from_checklist_id')->nullable()->after('is_archived');
            $table->foreign('archived_from_checklist_id')
                ->references('id')
                ->on('checklists')
                ->nullOnDelete();
        });
    }
};
