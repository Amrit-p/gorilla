<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Models\Job;
use App\Models\User;
use App\Support\CrmConstants;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixVerifiedJobsPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();
    }

    public function test_corrects_payment_status_for_jobs_verified_with_a_recorded_amount(): void
    {
        $partial = $this->verifiedJob(['charges' => 200, 'verified_amount' => 150, 'payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);
        $received = $this->verifiedJob(['charges' => 100, 'verified_amount' => 100, 'payment_status' => JobOperationalPaymentStatus::PENDING->value]);
        $alreadyCorrect = $this->verifiedJob(['charges' => 80, 'verified_amount' => 80, 'payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);

        $this->artisan('app:fix-verified-jobs-payment-status')->assertSuccessful();

        $this->assertSame(JobOperationalPaymentStatus::PARTIAL->value, $partial->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $received->refresh()->payment_status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $alreadyCorrect->refresh()->payment_status);
    }

    public function test_dry_run_previews_without_changing_anything(): void
    {
        $job = $this->verifiedJob(['charges' => 200, 'verified_amount' => 150, 'payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);

        $this->artisan('app:fix-verified-jobs-payment-status --dry-run')->assertSuccessful();

        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $job->refresh()->payment_status);
    }

    public function test_leaves_jobs_verified_without_a_recorded_amount_untouched(): void
    {
        $job = $this->verifiedJob(['charges' => 200, 'verified_amount' => null, 'payment_status' => JobOperationalPaymentStatus::RECEIVED->value]);

        $this->artisan('app:fix-verified-jobs-payment-status')->assertSuccessful();

        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $job->refresh()->payment_status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function verifiedJob(array $overrides): Job
    {
        return Job::query()->create(array_merge([
            'customer_name' => 'Job Client',
            'client_address' => '50 Job Lane',
            'scheduled_date' => now()->addDay()->toDateString(),
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'created_by' => $this->admin->id,
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
            'verified_date' => now()->toDateString(),
        ], $overrides));
    }
}
