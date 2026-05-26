<?php

use App\Enums\JobWorkflowStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'Pending' => JobWorkflowStatus::STARTED->value,
            'Assigned' => JobWorkflowStatus::STARTED->value,
            'En Route' => JobWorkflowStatus::STARTED->value,
            'On Site' => JobWorkflowStatus::STARTED->value,
            'Completed' => JobWorkflowStatus::COMPLETED->value,
            'Cancelled' => JobWorkflowStatus::HOLD->value,
        ];

        foreach ($map as $from => $to) {
            DB::table('service_jobs')->where('status', $from)->update(['status' => $to]);
        }

        DB::table('service_jobs')
            ->whereNotIn('status', JobWorkflowStatus::values())
            ->update(['status' => JobWorkflowStatus::STARTED->value]);
    }

    public function down(): void
    {
        $map = [
            JobWorkflowStatus::STARTED->value => 'Pending',
            JobWorkflowStatus::HOLD->value => 'Cancelled',
            JobWorkflowStatus::COMPLETED->value => 'Completed',
        ];

        foreach ($map as $from => $to) {
            DB::table('service_jobs')->where('status', $from)->update(['status' => $to]);
        }
    }
};
