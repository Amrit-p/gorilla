<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobImageKind;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\UserEfficiency;
use App\Models\Checklist;
use App\Models\Client;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Services\JobImageManagementService;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobImageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $mower;

    private User $otherMower;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(ChecklistSeeder::class);

        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
        $this->mower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->mower->assignRole(CrmRoles::MOWER);

        $this->otherMower = User::factory()->create([
            'is_active' => true,
            'efficiency' => UserEfficiency::GOOD->value,
        ]);
        $this->otherMower->assignRole(CrmRoles::MOWER);

        $this->completeChecklistForMower($this->mower);
        $this->completeChecklistForMower($this->otherMower);
    }

    public function test_upload_stores_compressed_full_and_thumbnail_under_jobs_path(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $response = $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [
                    UploadedFile::fake()->image('site.jpg', 2400, 1800),
                    UploadedFile::fake()->image('site-2.png', 600, 400),
                ],
            ])
            ->assertOk()
            ->assertJsonStructure(['images' => [['id', 'url', 'thumb_url', 'path', 'thumb_path']]]);

        $job->refresh();
        $this->assertCount(2, $job->before_images);

        $first = $job->before_images[0];
        $this->assertStringStartsWith('jobs/'.$job->id.'/before/', $first['path']);
        $this->assertStringStartsWith('jobs/'.$job->id.'/before/thumbs/', $first['thumb_path']);
        Storage::disk('public')->assertExists($first['path']);
        Storage::disk('public')->assertExists($first['thumb_path']);

        $images = $response->json('images');
        $this->assertNotSame($images[0]['url'], $images[0]['thumb_url']);
    }

    public function test_delete_removes_files_and_metadata(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $upload = $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [UploadedFile::fake()->image('del.jpg')],
            ])
            ->assertOk();

        $imageId = $upload->json('images.0.id');
        $path = $upload->json('images.0.path');
        $thumbPath = $upload->json('images.0.thumb_path');

        $this->actingAs($this->mower)
            ->deleteJson(route('mower.jobs.images.before.destroy', [$job, $imageId]))
            ->assertOk()
            ->assertJsonPath('images', []);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($thumbPath);

        $job->refresh();
        $this->assertSame([], $job->before_images);
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf')],
            ])
            ->assertUnprocessable();
    }

    public function test_oversized_file_is_rejected(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.after', $job), [
                'images' => [UploadedFile::fake()->image('huge.jpg')->size(11000)],
            ])
            ->assertUnprocessable();
    }

    public function test_unassigned_mower_cannot_upload_or_delete(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->otherMower);

        $this->actingAs($this->mower)
            ->postJson(route('mower.jobs.images.before', $job), [
                'images' => [UploadedFile::fake()->image('x.jpg')],
            ])
            ->assertForbidden();

        $job->before_images = [[
            'id' => 'test-id',
            'path' => 'jobs/'.$job->id.'/before/test-id.jpg',
            'thumb_path' => 'jobs/'.$job->id.'/before/thumbs/test-id.jpg',
            'size_bytes' => 100,
            'uploaded_at' => now()->toIso8601String(),
        ]];
        $job->save();

        $this->actingAs($this->mower)
            ->deleteJson(route('mower.jobs.images.before.destroy', [$job, 'test-id']))
            ->assertForbidden();
    }

    public function test_office_manager_can_delete_without_assignment(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        $service = app(JobImageManagementService::class);
        $images = $service->upload($job, JobImageKind::AFTER, [
            UploadedFile::fake()->image('after.jpg'),
        ], $this->mower);

        $imageId = $images[0]['id'];

        $this->actingAs($this->admin)
            ->deleteJson(route('mower.jobs.images.after.destroy', [$job, $imageId]))
            ->assertOk();

        $job->refresh();
        $this->assertSame([], $job->after_images);
    }

    public function test_present_all_resolves_thumb_and_full_urls(): void
    {
        Storage::fake('public');
        $job = $this->createAssignedJob($this->mower);

        app(JobImageManagementService::class)->upload(
            $job,
            JobImageKind::BEFORE,
            [UploadedFile::fake()->image('present.jpg')],
            $this->mower
        );

        $presented = app(JobImageManagementService::class)->presentAllForJob($job->fresh());

        $this->assertCount(1, $presented['before']);
        $this->assertArrayHasKey('url', $presented['before'][0]);
        $this->assertArrayHasKey('thumb_url', $presented['before'][0]);
    }

    private function completeChecklistForMower(User $user): void
    {
        $checklist = Checklist::safetyChecklist();

        foreach ($checklist->points as $point) {
            MowerChecklistSubmission::create([
                'user_id' => $user->id,
                'checklist_point_id' => $point->id,
                'date' => now()->toDateString(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAssignedJob(User $mower, array $overrides = []): Job
    {
        $client = Client::query()->create([
            'name' => 'Image Client '.$mower->id,
            'address' => '20 Photo Ln',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => 'No',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Done',
            'customer_type' => JobCustomerType::EASY->value,
        ]);

        $job = Job::query()->create(array_merge([
            'client_id' => $client->id,
            'client_address' => '20 Photo Ln',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'status' => JobWorkflowStatus::STARTED->value,
        ], $overrides));

        $job->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $job->scheduled_date,
            'assignment_status' => $job->status,
        ]);

        return $job;
    }
}
