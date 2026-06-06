<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_points', function (Blueprint $table): void {
            $table->boolean('is_archived')->default(false)->after('sort_order');
            $table->unsignedBigInteger('archived_from_point_id')->nullable()->after('is_archived');

            $table->foreign('archived_from_point_id')
                ->references('id')
                ->on('checklist_points')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('checklist_points', function (Blueprint $table): void {
            $table->dropForeign(['archived_from_point_id']);
            $table->dropColumn(['is_archived', 'archived_from_point_id']);
        });
    }
};
