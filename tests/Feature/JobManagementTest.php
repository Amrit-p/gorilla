<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\User;
use App\Notifications\JobDeletedNotification;
use App\Notifications\JobMowerAssignmentChangedNotification;
use App\Support\CrmConstants;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class JobManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(RecurrenceSeeder::class);
        $this->seed(JobLevelSeeder::class);
        $this->admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();
        $this->mower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->mower->assignRole(CrmRoles::MOWER);
    }

    public function test_job_can_be_created_via_ajax(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), $this->jobPayload())
            ->assertCreated()
            ->assertJsonPath('job.customer_name', 'Job Client');

        $this->assertDatabaseHas('service_jobs', [
            'customer_name' => 'Job Client',
            'phone' => '555-0100',
            'status' => JobWorkflowStatus::PENDING->value,
        ]);
    }

    public function test_job_can_be_created_without_client_using_contact_fields(): void
    {
        $payload = $this->jobPayload();
        $payload['customer_name'] = 'Standalone Customer';
        $payload['phone'] = '555-9999';
        $payload['email'] = 'standalone@example.com';

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), $payload)
            ->assertCreated()
            ->assertJsonPath('job.customer_name', 'Standalone Customer');

        $this->assertDatabaseHas('service_jobs', [
            'customer_name' => 'Standalone Customer',
            'phone' => '555-9999',
            'email' => 'standalone@example.com',
        ]);
    }

    public function test_job_can_be_created_without_customer_name_or_phone(): void
    {
        $payload = $this->jobPayload();
        unset($payload['customer_name'], $payload['phone'], $payload['email']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), $payload)
            ->assertCreated();

        $this->assertDatabaseHas('service_jobs', [
            'customer_name' => null,
            'phone' => null,
            'email' => null,
            'client_address' => '50 Job Lane',
        ]);
    }

    public function test_job_can_be_created_without_optional_operational_fields(): void
    {
        $payload = $this->jobPayload();
        unset($payload['job_level_id'], $payload['scheduled_time'], $payload['payment_status']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), $payload)
            ->assertCreated();

        $this->assertDatabaseHas('service_jobs', [
            'job_level_id' => null,
            'scheduled_time' => null,
            'payment_status' => null,
        ]);
    }

    public function test_job_creation_does_not_require_pending_reason_when_payment_status_pending_or_partial(): void
    {
        foreach ([JobOperationalPaymentStatus::PENDING, JobOperationalPaymentStatus::PARTIAL] as $status) {
            $payload = array_merge($this->jobPayload(), [
                'payment_status' => $status->value,
            ]);
            unset($payload['payment_pending_reason']);

            $this->actingAs($this->admin)
                ->postJson(route('admin.jobs.store'), $payload)
                ->assertCreated();
        }
    }

    public function test_job_update_does_not_require_pending_reason_when_payment_status_pending_or_partial(): void
    {
        $job = $this->createJob();

        $payload = array_merge($this->jobPayload(), [
            'payment_status' => JobOperationalPaymentStatus::PARTIAL->value,
            'route_sequence' => 0,
            'priority' => 'Medium',
            'status' => JobWorkflowStatus::STARTED->value,
        ]);
        unset($payload['payment_pending_reason']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.jobs.update', $job), $payload)
            ->assertOk();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.jobs.update', $job), array_merge($payload, [
                'payment_pending_reason' => 'Second installment due next week.',
            ]))
            ->assertOk();

        $job->refresh();
        $this->assertSame('Second installment due next week.', $job->payment_pending_reason);
    }

    public function test_mower_assignment_on_create(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.store'), array_merge($this->jobPayload(), [
                'employee_ids' => [$this->mower->id],
                'done_by_user_id' => $this->mower->id,
            ]))
            ->assertCreated();

        $job = Job::query()->where('customer_name', 'Job Client')->where('phone', '555-0100')->firstOrFail();
        $this->assertTrue($job->assignedEmployees()->where('users.id', $this->mower->id)->exists());
        $this->assertSame($this->mower->id, $job->done_by_user_id);
    }

    public function test_bulk_assign_notifies_the_assigned_mower(): void
    {
        Notification::fake();
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.assign'), [
                'job_ids' => [$job->id],
                'done_by_user_id' => $this->mower->id,
                'employee_ids' => [$this->mower->id],
            ])
            ->assertOk();

        Notification::assertSentTo($this->mower, JobMowerAssignmentChangedNotification::class);
    }

    public function test_status_update_uses_workflow_statuses(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.status.update'), [
                'job_ids' => [$job->id],
                'status' => JobWorkflowStatus::HOLD->value,
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.status.update'), [
                'job_ids' => [$job->id],
                'status' => JobWorkflowStatus::COMPLETED->value,
            ])
            ->assertOk();

        $job->refresh();
        $this->assertSame(JobWorkflowStatus::COMPLETED->value, $job->status);
    }

    public function test_list_filter_today_returns_scheduled_jobs(): void
    {
        Job::query()->create(array_merge($this->jobPayload(), [
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::STARTED->value,
        ]));
        Job::query()->create(array_merge($this->jobPayload(), [
            'scheduled_date' => now()->addWeek()->toDateString(),
            'status' => JobWorkflowStatus::STARTED->value,
        ]));

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['list_scope' => 'today']))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString(now()->format('d M'), $html);
    }

    public function test_jobs_index_shows_customer_name_from_job_contact_fields(): void
    {
        Job::query()->create(array_merge($this->jobPayload(), [
            'customer_name' => 'Amritsar Contact',
            'phone' => '9876543210',
            'client_address' => 'Amritsar, Punjab, India',
            'scheduled_date' => now()->toDateString(),
            'status' => JobWorkflowStatus::HOLD->value,
        ]));

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index'))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString('Amritsar Contact', $html);
        $this->assertStringContainsString('9876543210', $html);
        $this->assertStringNotContainsString('>N/A<', $html);
        // The row action menu ships the status button seeded with the job's current status.
        $this->assertStringContainsString('class="status-job', $html);
        $this->assertStringContainsString('data-status="'.JobWorkflowStatus::HOLD->value.'"', $html);
    }

    public function test_jobs_index_footer_shows_total_revenue_for_the_page(): void
    {
        Job::query()->create(array_merge($this->jobPayload(), [
            'customer_name' => 'Revenue Client One',
            'charges' => 100.50,
        ]));
        Job::query()->create(array_merge($this->jobPayload(), [
            'customer_name' => 'Revenue Client Two',
            'charges' => 49.50,
        ]));

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index'))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString('id="jobs-revenue-total">150.00<', $html);
    }

    public function test_mower_suggestions_endpoint_returns_recommendation(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.mower-suggestions', [
                'scheduled_date' => now()->toDateString(),
                'estimated_duration_minutes' => 90,
            ]))
            ->assertOk()
            ->assertJsonStructure(['suggestion', 'workloads']);
    }

    public function test_mower_cannot_create_jobs_but_can_view(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->mower)
            ->get(route('admin.jobs.index'))
            ->assertOk();

        $this->actingAs($this->mower)
            ->get(route('admin.jobs.create'))
            ->assertForbidden();
    }

    public function test_job_show_page_renders_timeline(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.status.update'), [
                'job_ids' => [$job->id],
                'status' => JobWorkflowStatus::HOLD->value,
            ]);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee('Activity timeline')
            ->assertSee('Job tracking');
    }

    public function test_client_remarks_endpoint_returns_the_jobs_mower_remarks(): void
    {
        $job = $this->createJob();
        $job->update(['mower_remarks' => 'Target job remark.']);

        $other = $this->createJob();
        $other->update(['mower_remarks' => 'Other job remark.']);

        $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.client-remarks', ['job_id' => $job->id]))
            ->assertOk()
            ->assertJsonPath('lastRemark.description', 'Target job remark.')
            ->assertJsonCount(1, 'allRemarks');
    }

    public function test_client_remarks_endpoint_returns_null_when_job_has_no_mower_remarks(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.client-remarks', ['job_id' => $job->id]))
            ->assertOk()
            ->assertJsonPath('lastRemark', null)
            ->assertJsonCount(0, 'allRemarks');
    }

    public function test_admin_can_update_job_office_remarks(): void
    {
        $job = $this->createJob();
        $job->update(['status' => JobWorkflowStatus::PENDING->value]);
        $job->assignedEmployees()->attach($this->mower->id, [
            'assignment_date' => $job->scheduled_date,
            'assignment_status' => $job->status,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.jobs.remarks.update', $job), [
                'office_remarks' => 'Gate code changed to 4321.',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Remarks updated successfully.');

        $this->assertSame('Gate code changed to 4321.', $job->refresh()->office_remarks);
    }

    public function test_bulk_schedule_updates_scheduled_date(): void
    {
        $job = $this->createJob();
        $newDate = now()->addDays(3)->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => $newDate,
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 1)
            ->assertJsonPath('message', 'Job rescheduled successfully.');

        $job->refresh();
        $this->assertSame($newDate, $job->scheduled_date->toDateString());
    }

    public function test_bulk_schedule_updates_scheduled_time_when_provided(): void
    {
        $job = $this->createJob();
        $newDate = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => $newDate,
                'scheduled_time' => '14:30',
            ])
            ->assertOk();

        $job->refresh();
        $this->assertSame($newDate, $job->scheduled_date->toDateString());
        $this->assertSame('14:30', $job->scheduled_time);
    }

    public function test_bulk_schedule_multiple_jobs(): void
    {
        $jobA = $this->createJob();
        $jobB = $this->createJob();
        $newDate = now()->addWeek()->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$jobA->id, $jobB->id],
                'scheduled_date' => $newDate,
            ])
            ->assertOk()
            ->assertJsonPath('updated_count', 2)
            ->assertJsonPath('message', 'Selected jobs rescheduled successfully.');

        $this->assertSame($newDate, $jobA->refresh()->scheduled_date->toDateString());
        $this->assertSame($newDate, $jobB->refresh()->scheduled_date->toDateString());
    }

    public function test_bulk_schedule_resets_status_to_pending(): void
    {
        $job = $this->createJob();
        $job->update(['status' => JobWorkflowStatus::HOLD->value]);
        $newDate = now()->addDays(3)->toDateString();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => $newDate,
            ])
            ->assertOk();

        $job->refresh();
        $this->assertSame(JobWorkflowStatus::PENDING->value, $job->status);
    }

    public function test_bulk_schedule_rejects_a_past_date(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => now()->subDay()->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('scheduled_date');
    }

    public function test_bulk_schedule_allows_todays_date(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => now()->toDateString(),
            ])
            ->assertOk();
    }

    public function test_mower_cannot_bulk_schedule_jobs(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->mower)
            ->postJson(route('admin.jobs.bulk.schedule'), [
                'job_ids' => [$job->id],
                'scheduled_date' => now()->addDay()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_admin_can_verify_a_job(): void
    {
        $job = $this->createVerifiableJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified' => 1,
                'verified_date' => '2026-08-30',
                'verified_amount' => 150.50,
                'comment' => 'Looks good.',
            ])
            ->assertOk()
            ->assertJsonPath('verified_count', 1)
            ->assertJsonPath('message', 'Job verified successfully.');

        $job->refresh();
        $this->assertNotNull($job->verified_at);
        $this->assertSame($this->admin->id, $job->verified_by);
        $this->assertSame('2026-08-30', $job->verified_date->toDateString());
        $this->assertSame('150.50', $job->verified_amount);
        $this->assertSame('Looks good.', $job->verification_comment);
    }

    public function test_verifying_with_amount_below_charges_marks_payment_partial(): void
    {
        $job = $this->createVerifiableJob();
        $job->update(['charges' => 200]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified' => 1,
                'verified_date' => '2026-08-30',
                'verified_amount' => 150,
            ])
            ->assertOk();

        $this->assertSame(JobOperationalPaymentStatus::PARTIAL->value, $job->refresh()->payment_status);
    }

    public function test_verifying_with_amount_meeting_or_exceeding_charges_marks_payment_received(): void
    {
        $job = $this->createVerifiableJob();
        $job->update(['charges' => 200, 'payment_status' => JobOperationalPaymentStatus::PARTIAL->value]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified' => 1,
                'verified_date' => '2026-08-30',
                'verified_amount' => 200,
            ])
            ->assertOk();

        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $job->refresh()->payment_status);
    }

    public function test_verifying_a_job_requires_date_and_amount(): void
    {
        $job = $this->createVerifiableJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), ['job_ids' => [$job->id], 'verified' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['verified_date', 'verified_amount']);

        $this->assertNull($job->refresh()->verified_at);
    }

    public function test_job_cannot_be_verified_unless_completed(): void
    {
        $job = $this->createVerifiableJob();
        $job->update(['status' => JobWorkflowStatus::STARTED->value]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified' => 1,
                'verified_date' => '2026-08-30',
                'verified_amount' => 150,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'None of the selected jobs can be verified. Jobs must be Completed.');

        $this->assertNull($job->refresh()->verified_at);
    }

    public function test_admin_can_remove_job_verification(): void
    {
        $job = $this->createJob();
        $job->forceFill([
            'verified_at' => now(),
            'verified_by' => $this->admin->id,
            'verified_date' => '2026-08-30',
            'verified_amount' => 150,
            'verification_comment' => 'Looks good.',
        ])->save();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), ['job_ids' => [$job->id], 'verified' => 0])
            ->assertOk()
            ->assertJsonPath('verified_count', 1)
            ->assertJsonPath('message', 'Job verification removed.');

        $job->refresh();
        $this->assertNull($job->verified_at);
        $this->assertNull($job->verified_by);
        $this->assertNull($job->verified_date);
        $this->assertNull($job->verified_amount);
        $this->assertNull($job->verification_comment);
    }

    public function test_bulk_verify_verifies_eligible_jobs_and_skips_others(): void
    {
        $eligible = $this->createVerifiableJob();
        $ineligible = $this->createJob();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$eligible->id, $ineligible->id],
                'verified_date' => '2026-08-30',
                'verified_amount' => 150,
            ])
            ->assertOk()
            ->assertJsonPath('verified_count', 1)
            ->assertJsonPath('skipped_count', 1);

        $this->assertNotNull($eligible->refresh()->verified_at);
        $this->assertNull($ineligible->refresh()->verified_at);
    }

    public function test_bulk_verify_fails_when_no_jobs_eligible(): void
    {
        $job = $this->createJob();

        $this->withoutExceptionHandling();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified_date' => '2026-08-30',
                'verified_amount' => 150,
            ])
            ->assertStatus(422);

        $this->assertNull($job->refresh()->verified_at);
    }

    public function test_mower_cannot_bulk_verify_jobs(): void
    {
        $job = $this->createVerifiableJob();

        $this->actingAs($this->mower)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified_date' => '2026-08-30',
                'verified_amount' => 150,
            ])
            ->assertForbidden();

        $this->assertNull($job->refresh()->verified_at);
    }

    public function test_mower_cannot_verify_a_job(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->mower)
            ->postJson(route('admin.jobs.bulk.verify'), [
                'job_ids' => [$job->id],
                'verified' => 1,
                'verified_date' => '2026-08-30',
                'verified_amount' => 150,
            ])
            ->assertForbidden();

        $this->assertNull($job->refresh()->verified_at);
    }

    public function test_bulk_delete_soft_deletes_jobs(): void
    {
        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.jobs.bulk.destroy'), ['job_ids' => [$job->id]])
            ->assertOk()
            ->assertJsonPath('deleted_count', 1);

        $this->assertNotNull(Job::withTrashed()->find($job->id)->deleted_at);
        $this->assertNull(Job::query()->find($job->id));
    }

    public function test_bulk_delete_notifies_other_managers(): void
    {
        Notification::fake();

        $otherManager = User::factory()->create(['is_active' => true]);
        $otherManager->assignRole(CrmRoles::OFFICE_MANAGER);

        $job = $this->createJob();

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.jobs.bulk.destroy'), ['job_ids' => [$job->id]])
            ->assertOk();

        Notification::assertSentTo($otherManager, JobDeletedNotification::class);
        Notification::assertNotSentTo($this->admin, JobDeletedNotification::class);
    }

    public function test_completed_unverified_scope_returns_only_completed_jobs_without_verification(): void
    {
        $unverified = $this->createVerifiableJob();
        $verified = $this->createVerifiableJob();
        $verified->forceFill(['verified_at' => now(), 'verified_by' => $this->admin->id])->save();
        $pending = $this->createJob();

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['list_scope' => 'completed_unverified']))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString('data-job-id="'.$unverified->id.'"', $html);
        $this->assertStringNotContainsString('data-job-id="'.$verified->id.'"', $html);
        $this->assertStringNotContainsString('data-job-id="'.$pending->id.'"', $html);
    }

    public function test_office_manager_can_view_deleted_jobs_scope(): void
    {
        $activeJob = $this->createJob();
        $deletedJob = $this->createJob();
        $deletedJob->delete();

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['list_scope' => 'deleted']))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString('data-job-id="'.$deletedJob->id.'"', $html);
        $this->assertStringNotContainsString('data-job-id="'.$activeJob->id.'"', $html);
    }

    public function test_mower_cannot_view_deleted_jobs_scope(): void
    {
        $activeJob = $this->createJob();
        $deletedJob = $this->createJob();
        $deletedJob->delete();

        $response = $this->actingAs($this->mower)
            ->getJson(route('admin.jobs.index', ['list_scope' => 'deleted']))
            ->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString('data-job-id="'.$activeJob->id.'"', $html);
        $this->assertStringNotContainsString('data-job-id="'.$deletedJob->id.'"', $html);
    }

    public function test_office_manager_can_restore_a_deleted_job(): void
    {
        $job = $this->createJob();
        $job->delete();

        $this->actingAs($this->admin)
            ->postJson(route('admin.jobs.bulk.restore'), ['job_ids' => [$job->id]])
            ->assertOk()
            ->assertJsonPath('restored_count', 1);

        $this->assertDatabaseHas('service_jobs', ['id' => $job->id, 'deleted_at' => null]);
    }

    public function test_office_manager_can_permanently_delete_a_job(): void
    {
        $job = $this->createJob();
        $job->delete();

        $this->actingAs($this->admin)
            ->deleteJson(route('admin.jobs.bulk.force-destroy'), ['job_ids' => [$job->id]])
            ->assertOk()
            ->assertJsonPath('deleted_count', 1);

        $this->assertDatabaseMissing('service_jobs', ['id' => $job->id]);
    }

    public function test_mower_cannot_restore_or_force_delete_jobs(): void
    {
        $job = $this->createJob();
        $job->delete();

        $this->actingAs($this->mower)
            ->postJson(route('admin.jobs.bulk.restore'), ['job_ids' => [$job->id]])
            ->assertForbidden();

        $this->actingAs($this->mower)
            ->deleteJson(route('admin.jobs.bulk.force-destroy'), ['job_ids' => [$job->id]])
            ->assertForbidden();

        $this->assertNotNull(Job::withTrashed()->find($job->id)->deleted_at);
        $this->assertDatabaseHas('service_jobs', ['id' => $job->id]);
    }

    public function test_jobs_index_uses_the_selected_page_size(): void
    {
        config(['mowing.pagination_options' => [2, 5]]);
        $this->createJobs(3);

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['per_page' => 2]))
            ->assertOk()
            ->json('html');

        $this->assertSame(2, $this->countJobRows($html));
    }

    public function test_jobs_index_falls_back_to_the_default_page_size_for_an_unknown_value(): void
    {
        config(['mowing.pagination_options' => [2, 5], 'mowing.default_pagination' => 2]);
        $this->createJobs(3);

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['per_page' => 999]))
            ->assertOk()
            ->json('html');

        $this->assertSame(2, $this->countJobRows($html));
    }

    public function test_all_page_size_loads_one_chunk_and_offers_the_next(): void
    {
        config(['mowing.infinite_scroll_chunk' => 2]);
        $this->createJobs(5);

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['per_page' => 'all']))
            ->assertOk()
            ->json('html');

        // "All" must never dump every row into one response.
        $this->assertSame(2, $this->countJobRows($html));
        $this->assertStringContainsString('crm-scroll-sentinel', $html);
        $this->assertStringContainsString('data-next-page="2"', $html);
        $this->assertStringContainsString('data-last-page="3"', $html);
    }

    public function test_infinite_scroll_request_returns_the_next_chunk_of_rows_only(): void
    {
        config(['mowing.infinite_scroll_chunk' => 2]);
        $this->createJobs(5);

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', [
                'per_page' => 'all',
                'page' => 3,
                'rows_only' => 1,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertSame(1, $this->countJobRows($html));
        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringNotContainsString('crm-scroll-sentinel', $html);
    }

    public function test_infinite_scroll_chunk_past_the_last_page_renders_no_empty_state(): void
    {
        config(['mowing.infinite_scroll_chunk' => 2]);
        $this->createJobs(2);

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', [
                'per_page' => 'all',
                'page' => 5,
                'rows_only' => 1,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertSame(0, $this->countJobRows($html));
        $this->assertStringNotContainsString('No jobs found.', $html);
    }

    public function test_jobs_index_page_renders_the_page_size_picker(): void
    {
        config(['mowing.infinite_scroll_chunk' => 2]);
        $this->createJobs(5);

        $this->actingAs($this->admin)
            ->get(route('admin.jobs.index', ['per_page' => 'all']))
            ->assertOk()
            ->assertSee('id="crm-per-page"', false)
            ->assertSee('crm-scroll-sentinel', false)
            ->assertSee('data-next-page="2"', false);
    }

    public function test_scroll_chunks_keep_the_active_filters(): void
    {
        config(['mowing.infinite_scroll_chunk' => 2]);
        $this->createJobs(5);
        Job::query()->create(array_merge($this->jobPayload(), [
            'customer_name' => 'Unrelated Client',
            'created_by' => $this->admin->id,
        ]));

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', [
                'per_page' => 'all',
                'search' => 'Paged Client',
                'page' => 3,
                'rows_only' => 1,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertSame(1, $this->countJobRows($html));
        $this->assertStringNotContainsString('Unrelated Client', $html);
    }

    public function test_cards_only_request_returns_job_cards_without_table_markup(): void
    {
        $this->createJobs(3);

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['cards_only' => 1]))
            ->assertOk()
            ->json('html');

        $this->assertSame(3, $this->countJobCards($html));
        $this->assertStringNotContainsString('<table', $html);
    }

    public function test_cards_only_request_respects_search_filter(): void
    {
        $this->createJobs(2);
        Job::query()->create(array_merge($this->jobPayload(), [
            'customer_name' => 'Unrelated Client',
            'created_by' => $this->admin->id,
        ]));

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', [
                'search' => 'Paged Client',
                'cards_only' => 1,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertSame(2, $this->countJobCards($html));
        $this->assertStringNotContainsString('Unrelated Client', $html);
    }

    public function test_cards_only_request_with_no_matches_shows_empty_state(): void
    {
        $this->createJob();

        $html = (string) $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', [
                'search' => 'Nobody Matches This',
                'cards_only' => 1,
            ]))
            ->assertOk()
            ->json('html');

        $this->assertSame(0, $this->countJobCards($html));
        $this->assertStringContainsString('No jobs found', $html);
    }

    private function countJobRows(string $html): int
    {
        return substr_count($html, '<tr class="job-row');
    }

    private function countJobCards(string $html): int
    {
        return substr_count($html, 'class="job-card ');
    }

    /**
     * @return array<int, Job>
     */
    private function createJobs(int $count): array
    {
        return array_map(fn (int $index): Job => Job::query()->create(array_merge($this->jobPayload(), [
            'customer_name' => "Paged Client {$index}",
            'created_by' => $this->admin->id,
        ])), range(1, $count));
    }

    private function createJob(): Job
    {
        return Job::query()->create(array_merge($this->jobPayload(), [
            'created_by' => $this->admin->id,
        ]));
    }

    private function createVerifiableJob(): Job
    {
        $job = $this->createJob();
        $job->update([
            'status' => JobWorkflowStatus::COMPLETED->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
        ]);

        return $job;
    }

    /**
     * @return array<string, mixed>
     */
    private function jobPayload(): array
    {
        return [
            'customer_name' => 'Job Client',
            'phone' => '555-0100',
            'email' => 'job.client@example.com',
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
        ];
    }
}
