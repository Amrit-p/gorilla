<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\JobPendingFollowUpNotification;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendPendingJobFollowUpRemindersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(RecurrenceSeeder::class);
        $this->seed(JobLevelSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_sends_reminders_for_pending_jobs_with_no_update_for_2_days(): void
    {
        Notification::fake();

        $manager = User::query()->role(CrmRoles::OFFICE_MANAGER)->firstOrFail();

        $staleJob = $this->createPendingJob();
        DB::table('service_jobs')->where('id', $staleJob->id)->update(['updated_at' => now()->subDays(3)]);

        $this->artisan('app:send-pending-job-reminders')->assertSuccessful();

        Notification::assertSentTo($manager, JobPendingFollowUpNotification::class, function ($notification) use ($staleJob) {
            return $notification->job->id === $staleJob->id;
        });
    }

    public function test_does_not_send_reminders_for_recently_updated_pending_jobs(): void
    {
        Notification::fake();

        $this->createPendingJob();

        $this->artisan('app:send-pending-job-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_does_not_send_reminders_for_non_pending_jobs(): void
    {
        Notification::fake();

        $completedJob = $this->createPendingJob(['status' => JobWorkflowStatus::COMPLETED->value]);
        DB::table('service_jobs')->where('id', $completedJob->id)->update(['updated_at' => now()->subDays(3)]);

        $this->artisan('app:send-pending-job-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_outputs_summary_when_reminders_sent(): void
    {
        Notification::fake();

        $staleJob = $this->createPendingJob();
        DB::table('service_jobs')->where('id', $staleJob->id)->update(['updated_at' => now()->subDays(5)]);

        $this->artisan('app:send-pending-job-reminders')
            ->expectsOutputToContain('1 pending job(s)')
            ->assertSuccessful();
    }

    public function test_outputs_message_when_no_stale_jobs(): void
    {
        Notification::fake();

        $this->artisan('app:send-pending-job-reminders')
            ->expectsOutputToContain('No stale pending jobs found.')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPendingJob(array $overrides = []): Job
    {
        $client = Client::query()->create([
            'name' => 'Follow-Up Client',
            'address' => '50 Job Lane',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => 'Easy',
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
        ]);

        return Job::query()->create(array_merge([
            'client_id' => $client->id,
            'client_address' => '50 Job Lane',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'job_level_id' => JobLevel::query()->where('is_active', true)->value('id'),
            'status' => JobWorkflowStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ], $overrides));
    }
}
