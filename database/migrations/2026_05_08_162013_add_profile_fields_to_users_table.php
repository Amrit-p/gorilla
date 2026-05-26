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
        Schema::table('users', function (Blueprint $table) {
            // Profile fields needed for profile management screens.
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('timezone', 100)->nullable()->after('phone');

            // Activation flag will be used by admin user management module.
            $table->boolean('is_active')->default(true)->after('remember_token')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'timezone', 'is_active']);
        });
    }
};
