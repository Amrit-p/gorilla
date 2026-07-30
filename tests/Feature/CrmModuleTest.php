<?php

namespace Tests\Feature;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Exports\LeadsExport;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Lead;
use App\Models\Recurrence;
use App\Models\User;
use App\Support\ServiceTypes;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CrmModuleTest extends TestCase
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

    public function test_lead_create_page_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('Create Lead');
    }

    public function test_lead_can_be_stored_and_redirects(): void
    {
        $payload = $this->validLeadPayload();

        $this->actingAs($this->admin)
            ->post(route('admin.leads.store'), $payload)
            ->assertRedirect(route('admin.leads.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'address' => $payload['address'],
        ]);
        $lead = Lead::query()->where('address', $payload['address'])->first();
        $this->assertIsArray($lead?->service_types);
        $this->assertContains($payload['service_types'][0], $lead?->service_types ?? []);
    }

    public function test_lead_edit_page_is_accessible(): void
    {
        $lead = Lead::query()->create($this->validLeadPayload());

        $this->actingAs($this->admin)
            ->get(route('admin.leads.edit', $lead))
            ->assertOk()
            ->assertSee('Edit Lead');
    }

    public function test_lead_import_sample_excel_can_be_downloaded(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leads.import.sample'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertDownload('leads-import-sample.xlsx');

        $tmp = tempnam(sys_get_temp_dir(), 'leads_sample_').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();

            foreach (LeadsExport::COLUMNS as $col => $def) {
                $this->assertSame(
                    $def['header'],
                    $sheet->getCell($col.'4')->getValue(),
                    "Row 4 column {$col} header mismatch"
                );
            }
        } finally {
            @unlink($tmp);
        }
    }

    public function test_lead_create_route_is_not_captured_by_show_route(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/leads/create')
            ->assertOk()
            ->assertDontSee('No query results for model');
    }

    public function test_job_create_page_and_store(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.jobs.create'))
            ->assertOk()
            ->assertSee('Create Job')
            ->assertSee('Job details')
            ->assertDontSee('Select customer');

        $payload = $this->validJobPayload();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.jobs.store'), $payload)
            ->assertSessionHas('success');

        $job = Job::query()->where('phone', $payload['phone'])->firstOrFail();
        $response->assertRedirect(route('admin.jobs.show', $job));

        $this->assertDatabaseHas('service_jobs', [
            'customer_name' => $payload['customer_name'],
            'phone' => $payload['phone'],
            'client_address' => $payload['client_address'],
        ]);
    }

    public function test_job_create_route_is_not_captured_by_show_route(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/jobs/create')
            ->assertOk();
    }

    public function test_user_create_page_is_accessible(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Create User');
    }

    public function test_validation_assets_are_referenced_in_layout(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.leads.create'))
            ->assertOk()
            ->assertSee('jquery.validate.min.js', false)
            ->assertSee('crm-form-validation.js', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function validLeadPayload(): array
    {
        return [
            'client_name' => '123 Test Street',
            'address' => '123 Test Street',
            'latitude' => '43.6532000',
            'longitude' => '-79.3832000',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'job_type' => LeadJobType::REGULAR->value,
            'charges' => '50.00',
            'mobile_number' => '555-0100',
            'email' => 'lead@example.com',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => LeadPaymentStatus::DONE->value,
            'remarks' => 'Test lead',
            'lead_date' => now()->toDateString(),
            'lead_time' => '10:30',
            'status' => LeadStatus::NEW->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validJobPayload(): array
    {
        return [
            'customer_name' => 'CRM Job Customer',
            'phone' => '555-0456',
            'email' => 'crm.job@example.com',
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'job_level_id' => JobLevel::query()->where('is_active', true)->value('id'),
            'client_address' => '456 Client Ave',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => JobOperationalPaymentMode::CASH->value,
            'payment_status' => JobOperationalPaymentStatus::RECEIVED->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
        ];
    }
}
