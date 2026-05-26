<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->index(['customer_type', 'payment_status'], 'clients_type_payment_idx');
            $table->index('job_type', 'clients_job_type_idx');
            $table->index('parking_status', 'clients_parking_status_idx');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index(['status', 'assigned_sales_user_id'], 'leads_status_sales_idx');
        });

        Schema::table('job_user_assignments', function (Blueprint $table): void {
            $table->index(['user_id', 'job_id'], 'jua_user_job_idx');
        });

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->index(['payment_status', 'status'], 'service_jobs_payment_status_idx');
            $table->index('done_by_user_id', 'service_jobs_done_by_idx');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex('clients_type_payment_idx');
            $table->dropIndex('clients_job_type_idx');
            $table->dropIndex('clients_parking_status_idx');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex('leads_status_sales_idx');
        });

        Schema::table('job_user_assignments', function (Blueprint $table): void {
            $table->dropIndex('jua_user_job_idx');
        });

        Schema::table('service_jobs', function (Blueprint $table): void {
            $table->dropIndex('service_jobs_payment_status_idx');
            $table->dropIndex('service_jobs_done_by_idx');
        });
    }
};
