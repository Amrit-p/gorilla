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
        Schema::table('job_user_assignments', function (Blueprint $table) {
            $table->decimal('incentive_percentage', 5, 2)->default(0)->after('assignment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_user_assignments', function (Blueprint $table) {
            $table->dropColumn('incentive_percentage');
        });
    }
};
