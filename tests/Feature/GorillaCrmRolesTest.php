<?php

namespace Tests\Feature;

use App\Enums\JobWorkflowStatus;
use App\Models\Checklist;
use App\Models\Client;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use Database\Seeders\ChecklistSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GorillaCrmRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(ChecklistSeeder::class);
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

    private function userWithRole(string $roleLabel): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->syncRoles([$roleLabel]);

        return $user;
    }

    public function test_seeder_creates_gorilla_permissions_and_roles(): void
    {
        foreach (CrmPermissions::all() as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach (CrmRoles::all() as $role) {
            $this->assertDatabaseHas('roles', [
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $officeManager = Role::findByName(CrmRoles::OFFICE_MANAGER, 'web');
        $this->assertGreaterThanOrEqual(count(CrmPermissions::all()), $officeManager->permissions->count());
    }

    public function test_office_manager_can_access_admin_and_operations_routes(): void
    {
        $user = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.leads.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.clients.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.jobs.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.jobs.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_sales_manager_can_manage_leads_customers_and_view_jobs(): void
    {
        $user = $this->userWithRole(CrmRoles::SALES_MANAGER);

        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.leads.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.leads.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.clients.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.jobs.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.jobs.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.rbac.index'))->assertForbidden();
    }

    public function test_mower_can_view_jobs_and_mobile_but_not_create_jobs(): void
    {
        $user = $this->userWithRole(CrmRoles::MOWER);
        $this->completeChecklistForMower($user);

        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.jobs.index'))->assertOk();
        $this->actingAs($user)->get(route('employee.mobile.index'))->assertRedirect(route('mower.index'));
        $this->actingAs($user)->get(route('mower.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.jobs.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.leads.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.clients.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get(route('dashboard.index'))->assertRedirect(route('login'));
        $this->get(route('admin.leads.index'))->assertRedirect(route('login'));
    }

    public function test_crm_permission_middleware_blocks_unauthorized_user(): void
    {
        $sales = $this->userWithRole(CrmRoles::SALES_MANAGER);

        $this->actingAs($sales)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_crm_permission_helpers_on_user_model(): void
    {
        $mower = $this->userWithRole(CrmRoles::MOWER);

        $this->assertTrue($mower->canViewJobs());
        $this->assertFalse($mower->canManageJobRecords());
        $this->assertTrue($mower->canUploadJobImages());
        $this->assertFalse($mower->canManageLeads());
    }

    public function test_sales_manager_can_create_and_edit_jobs_but_not_delete_them(): void
    {
        $sales = $this->userWithRole(CrmRoles::SALES_MANAGER);

        $this->assertTrue($sales->canCreateJobs());
        $this->assertFalse($sales->canManageJobRecords());

        $client = Client::query()->create([
            'name' => 'Test Client',
            'address' => '1 Test St',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Pending',
        ]);
        $job = Job::query()->create([
            'client_id' => $client->id,
            'client_address' => '1 Test St',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => ['Mulching'],
            'status' => JobWorkflowStatus::STARTED->value,
            'priority' => 'Medium',
            'payment_mode' => 'Cash',
            'payment_status' => 'Received',
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
        ]);

        $this->assertTrue($sales->can('create', Job::class));
        $this->assertTrue($sales->can('update', $job));
        $this->assertFalse($sales->can('delete', $job));
        $this->assertFalse($sales->can('restore', $job));

        $this->actingAs($sales)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee(route('admin.jobs.edit', $job), false);

        $this->actingAs($sales)
            ->get(route('admin.jobs.edit', $job))
            ->assertOk();

        $this->actingAs($sales)
            ->get(route('admin.jobs.index'))
            ->assertOk()
            ->assertSee(route('admin.jobs.create'), false);
    }

    public function test_job_policy_upload_images_for_mower(): void
    {
        $mower = $this->userWithRole(CrmRoles::MOWER);
        $client = Client::query()->create([
            'name' => 'Test Client',
            'address' => '1 Test St',
            'service_types' => ['Mulching'],
            'weed_spray' => 'Yes',
            're_completion_days' => '14 days',
            'job_type' => 'Regular',
            'safety_concerns' => ['Pet'],
            'payment_mode' => 'Cash',
            'payment_status' => 'Pending',
        ]);
        $job = Job::query()->create([
            'client_id' => $client->id,
            'client_address' => '1 Test St',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => ['Mulching'],
            'status' => JobWorkflowStatus::STARTED->value,
            'priority' => 'Medium',
            'payment_mode' => 'Cash',
            'payment_status' => 'Received',
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
        ]);

        $this->assertFalse($mower->can('uploadImages', $job));

        $job->assignedEmployees()->attach($mower->id, [
            'assignment_date' => $job->scheduled_date,
            'assignment_status' => $job->status,
        ]);

        $this->assertTrue($mower->can('uploadImages', $job));
        $this->assertFalse($mower->can('update', $job));
    }
}
