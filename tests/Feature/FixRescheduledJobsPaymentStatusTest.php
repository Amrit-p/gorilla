<?php

namespace Tests\Feature;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Models\User;
use App\Support\CrmConstants;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixRescheduledJobsPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Job $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();

        $this->source = $this->job([
            'status' => JobWorkflowStatus::COMPLETED->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'received_amount' => 80,
        ]);
    }

    public function test_resets_inherited_payment_status_on_rescheduled_jobs(): void
    {
        $inheritedReceived = $this->rescheduledJob(['payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);
        $inheritedPartial = $this->rescheduledJob([
            'status' => JobWorkflowStatus::HOLD->value,
            'payment_status' => JobOperationalPaymentStatus::PARTIAL->value,
        ]);

        $this->artisan('app:fix-rescheduled-jobs-payment-status')->assertSuccessful();

        $this->assertSame(JobOperationalPaymentStatus::PENDING->value, $inheritedReceived->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::PENDING->value, $inheritedPartial->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $this->source->refresh()->payment_status);
    }

    public function test_dry_run_previews_without_changing_anything(): void
    {
        $job = $this->rescheduledJob(['payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);

        $this->artisan('app:fix-rescheduled-jobs-payment-status --dry-run')
            ->expectsOutputToContain('Would reset 1 job(s)')
            ->assertSuccessful();

        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $job->refresh()->payment_status);
    }

    public function test_leaves_payments_that_were_actually_recorded_untouched(): void
    {
        $completed = $this->rescheduledJob([
            'status' => JobWorkflowStatus::COMPLETED->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
        ]);
        $withReceivedAmount = $this->rescheduledJob([
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'received_amount' => 80,
        ]);
        $withPartialPayment = $this->rescheduledJob([
            'payment_status' => JobOperationalPaymentStatus::PARTIAL->value,
            'received_amount' => 40,
        ]);
        $verified = $this->rescheduledJob([
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'verified_at' => now(),
        ]);
        $notRescheduled = $this->job(['payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);

        $this->artisan('app:fix-rescheduled-jobs-payment-status')
            ->expectsOutputToContain('No rescheduled jobs carry an inherited payment status.')
            ->assertSuccessful();

        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $completed->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $withReceivedAmount->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::PARTIAL->value, $withPartialPayment->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $verified->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $notRescheduled->refresh()->payment_status);
    }

    /** @param array<string, mixed> $overrides */
    private function rescheduledJob(array $overrides = []): Job
    {
        return $this->job(array_merge(['rescheduled_from_job_id' => $this->source->id], $overrides));
    }

    /** @param array<string, mixed> $overrides */
    private function job(array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'customer_name' => 'Test Client',
            'phone' => '555-0000',
            'client_address' => '1 Test Rd',
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::PENDING->value,
            'payment_status' => JobOperationalPaymentStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
