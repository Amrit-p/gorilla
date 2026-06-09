<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table): void {
            if (! Schema::hasColumn('checklists', 'slug')) {
                $table->string('slug', 120)->unique()->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table): void {
            $table->dropColumn('slug');
        });
    }
};
