<?php

namespace Tests\Feature;

use App\Enums\JobWorkflowStatus;
use App\Models\Checklist;
use App\Models\Job;
use App\Models\MowerChecklistSubmission;
use App\Models\User;
use App\Support\CrmConstants;
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

        $admin = Role::findByName(CrmRoles::ADMIN, 'web');
        $this->assertGreaterThanOrEqual(count(CrmPermissions::all()), $admin->permissions->count());

        $officeManager = Role::findByName(CrmRoles::OFFICE_MANAGER, 'web');
        $this->assertLessThan(count(CrmPermissions::all()), $officeManager->permissions->count());
    }

    public function test_office_manager_can_access_admin_and_operations_routes(): void
    {
        $user = User::query()->where('email', CrmConstants::OFFICE_MANAGER_EMAIL)->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.leads.index'))
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

    public function test_office_manager_cannot_reach_revenue_and_payroll_screens(): void
    {
        $user = User::query()->where('email', CrmConstants::OFFICE_MANAGER_EMAIL)->firstOrFail();

        $this->actingAs($user)->get(route('reports.mower.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.salary-calculator.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.employee-bonuses.index'))->assertForbidden();
        $this->actingAs($user)->get(route('dashboard.analytics.charts'))->assertForbidden();

        $this->assertFalse($user->can(CrmPermissions::VIEW_BUSINESS_ANALYTICS));
        $this->assertFalse($user->can(CrmPermissions::VIEW_MOWER_REPORT));
        $this->assertFalse($user->can(CrmPermissions::MANAGE_SALARY_CALCULATOR));
        $this->assertFalse($user->can(CrmPermissions::MANAGE_EMPLOYEE_BONUSES));
    }

    public function test_office_manager_dashboard_hides_revenue_cards_and_business_analytics(): void
    {
        $user = User::query()->where('email', CrmConstants::OFFICE_MANAGER_EMAIL)->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertDontSee('Total revenue (MTD)')
            ->assertDontSee('Business Analytics')
            ->assertDontSee('Jobs by status')
            ->assertDontSee(route('reports.mower.index'), false)
            ->assertDontSee(route('admin.salary-calculator.index'), false)
            ->assertDontSee(route('admin.employee-bonuses.index'), false)
            ->assertSee("Today's schedule", false);
    }

    public function test_admin_dashboard_still_shows_revenue_cards_and_business_analytics(): void
    {
        $user = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Total revenue (MTD)')
            ->assertSee('Business Analytics')
            ->assertSee('Jobs by status');

        $this->actingAs($user)->get(route('reports.mower.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.salary-calculator.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.employee-bonuses.index'))->assertOk();
    }

    public function test_office_manager_keeps_job_payment_visibility(): void
    {
        $user = User::query()->where('email', CrmConstants::OFFICE_MANAGER_EMAIL)->firstOrFail();

        $job = Job::query()->create([
            'customer_name' => 'Paying Client',
            'phone' => '555-0199',
            'email' => 'paying.client@example.com',
            'client_address' => '9 Test St',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => ['Mulching'],
            'status' => JobWorkflowStatus::COMPLETED->value,
            'priority' => 'Medium',
            'payment_mode' => 'Cash',
            'payment_status' => 'Pending',
            'parking_status' => 'Easy',
            'customer_type' => 'Easy',
            'charges' => 250.00,
        ]);

        $this->actingAs($user)
            ->get(route('admin.jobs.show', $job))
            ->assertOk()
            ->assertSee('250.00');

        $this->assertTrue($user->can(CrmPermissions::VERIFY_JOBS));
    }

    public function test_office_manager_cannot_assign_the_admin_role(): void
    {
        $officeManager = User::query()->where('email', CrmConstants::OFFICE_MANAGER_EMAIL)->firstOrFail();
        $target = $this->userWithRole(CrmRoles::MOWER);

        $this->actingAs($officeManager)
            ->patchJson(route('admin.rbac.users.role.update', $target), ['role' => CrmRoles::ADMIN])
            ->assertUnprocessable();

        $this->assertFalse($target->fresh()->hasRole(CrmRoles::ADMIN));

        $admin = User::query()->where('email', CrmConstants::ADMIN_EMAIL)->firstOrFail();

        $this->actingAs($admin)
            ->patchJson(route('admin.rbac.users.role.update', $target), ['role' => CrmRoles::ADMIN])
            ->assertOk();

        $this->assertTrue($target->fresh()->hasRole(CrmRoles::ADMIN));
    }

    public function test_sales_manager_can_manage_leads_and_view_jobs(): void
    {
        $user = $this->userWithRole(CrmRoles::SALES_MANAGER);

        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.leads.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.leads.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.jobs.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.jobs.create'))->assertOk();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.rbac.index'))->assertForbidden();
    }

    public function test_mower_can_view_jobs_and_mobile_but_not_create_jobs(): void
    {
        $user = $this->userWithRole(CrmRoles::MOWER);
        $this->completeChecklistForMower($user);

        $this->actingAs($user)->get(route('dashboard.index'))->assertRedirect(route('mower.index'));
        $this->actingAs($user)->get(route('admin.jobs.index'))->assertOk();
        $this->actingAs($user)->get(route('employee.mobile.index'))->assertRedirect(route('mower.index'));
        $this->actingAs($user)->get(route('mower.index'))->assertOk();

        $this->actingAs($user)->get(route('admin.jobs.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.leads.index'))->assertForbidden();
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

        $job = Job::query()->create([
            'customer_name' => 'Test Client',
            'phone' => '555-0100',
            'email' => 'test.client@example.com',
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
        $job = Job::query()->create([
            'customer_name' => 'Test Client',
            'phone' => '555-0100',
            'email' => 'test.client@example.com',
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
