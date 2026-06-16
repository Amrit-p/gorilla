<?php

namespace Tests\Feature;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\JobCustomerType;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadReCompletionDays;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Exports\ClientsExport;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\ClientRating;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\Lead;
use App\Models\Recurrence;
use App\Models\User;
use App\Support\ServiceTypes;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
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

    public function test_customer_creation_assigns_unique_id(): void
    {
        $payload = $this->validCustomerPayload();

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertRedirect();

        $client = Client::query()->where('address', $payload['address'])->first();
        $this->assertNotNull($client);
        $this->assertSame(2001, $client->customer_unique_id);
        $this->assertSame(ClientCustomerType::HARD->value, $client->customer_type);
    }

    public function test_customer_can_be_created_with_client_rating(): void
    {
        $rating = ClientRating::create(['name' => 'Excellent', 'is_active' => true, 'sort_order' => 1]);

        $payload = array_merge($this->validCustomerPayload(), [
            'client_rating_id' => $rating->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertRedirect();

        $client = Client::query()->where('address', $payload['address'])->firstOrFail();
        $this->assertSame($rating->id, $client->client_rating_id);
    }

    public function test_customer_creation_rejects_invalid_client_rating(): void
    {
        $payload = array_merge($this->validCustomerPayload(), [
            'client_rating_id' => 999999,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertSessionHasErrors('client_rating_id');
    }

    public function test_lead_conversion_sets_customer_fields(): void
    {
        $lead = Lead::query()->create([
            'client_name' => 'Converted Customer',
            'address' => '88 Convert St',
            'lead_date' => now()->toDateString(),
            'lead_time' => '14:00',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => ClientPaymentStatus::PENDING->value,
            'remarks' => 'Converted remarks',
            'status' => LeadStatus::NEW->value,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.leads.status.update', $lead), ['status' => LeadStatus::WON->value])
            ->assertOk();

        $client = Client::query()->where('lead_id', $lead->id)->first();
        $this->assertNotNull($client);
        $this->assertNotNull($client->customer_unique_id);
        $this->assertSame(ClientCustomerType::DONT_KNOW->value, $client->customer_type);
        $this->assertSame('Converted remarks', $client->special_remarks);
        $this->assertSame($lead->equipment_type_id, $client->equipment_type_id);
    }

    public function test_customer_advanced_filters_return_matching_rows(): void
    {
        Client::query()->create(array_merge($this->validCustomerPayload(), [
            'address' => 'Easy Filter Ave',
            'customer_type' => ClientCustomerType::EASY->value,
        ]));

        Client::query()->create(array_merge($this->validCustomerPayload(), [
            'address' => 'Hard Filter Blvd',
            'customer_type' => ClientCustomerType::HARD->value,
        ]));

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.clients.index', ['customer_type' => ClientCustomerType::HARD->value]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $html = (string) $response->json('html');
        $this->assertStringContainsString('Hard Filter Blvd', $html);
        $this->assertStringNotContainsString('Easy Filter Ave', $html);
    }

    public function test_customer_details_page_renders_tabs(): void
    {
        $client = Client::query()->create($this->validCustomerPayload());

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee('Details')
            ->assertSee('Jobs')
            ->assertSee('#'.$client->customer_unique_id, false);
    }

    public function test_jobs_tab_loads_via_ajax_with_statistics(): void
    {
        $client = Client::query()->create($this->validCustomerPayload());

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => JobCustomerType::EASY->value,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => 'Received',
            'status' => 'Completed',
            'created_by' => $this->admin->id,
        ]);

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '11:00',
            'estimated_duration_minutes' => 45,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => 'Easy',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => 'Received',
            'status' => 'Pending',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jobs.index', ['client_id' => $client->id]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('Completed', $response->json('html'));
    }

    public function test_customer_show_displays_job_statistics(): void
    {
        $client = Client::query()->create($this->validCustomerPayload());

        Job::query()->create([
            'client_id' => $client->id,
            'client_address' => $client->address,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'estimated_duration_minutes' => 60,
            'required_services' => [ServiceTypes::all()[0]],
            'parking_status' => JobParkingStatus::EASY->value,
            'customer_type' => 'Easy',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => 'Received',
            'status' => 'Completed',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee('Total jobs')
            ->assertSee('Completed');
    }

    public function test_customer_creation_stores_uploaded_documents(): void
    {
        Storage::fake(ClientDocument::DISK);

        $payload = array_merge($this->validCustomerPayload(), [
            'documents' => [
                UploadedFile::fake()->create('contract.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('site.jpg'),
            ],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertRedirect();

        $client = Client::query()->where('address', $payload['address'])->firstOrFail();
        $this->assertCount(2, $client->documents);

        foreach ($client->documents as $document) {
            Storage::disk(ClientDocument::DISK)->assertExists($document->file_path);
            $this->assertSame($this->admin->id, $document->uploaded_by);
        }
    }

    public function test_uploaded_document_can_be_downloaded(): void
    {
        Storage::fake(ClientDocument::DISK);

        $payload = array_merge($this->validCustomerPayload(), [
            'documents' => [UploadedFile::fake()->create('manual.pdf', 50, 'application/pdf')],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertRedirect();

        $client = Client::query()->where('address', $payload['address'])->firstOrFail();
        $document = $client->documents()->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.clients.documents.download', [$client, $document]))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="manual.pdf"');
    }

    public function test_customer_creation_rejects_disallowed_document_types(): void
    {
        Storage::fake(ClientDocument::DISK);

        $payload = array_merge($this->validCustomerPayload(), [
            'documents' => [UploadedFile::fake()->create('malware.exe', 10)],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.clients.store'), $payload)
            ->assertSessionHasErrors('documents.0');

        $this->assertSame(0, ClientDocument::query()->count());
    }

    public function test_import_sample_file_can_be_downloaded(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.clients.import.sample'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_rejects_non_xlsx_file(): void
    {
        $file = UploadedFile::fake()->create('clients.csv', 10, 'text/csv');

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('import_file');
    }

    public function test_import_creates_customers_from_valid_xlsx(): void
    {
        $recurrence = Recurrence::query()->where('is_active', true)->firstOrFail();
        $equipment = EquipmentType::query()->where('is_active', true)->firstOrFail();

        $file = $this->makeImportXlsx([[
            '',            // A: #
            '',            // B: Customer ID
            'Import Test', // C: Name
            'importtest@example.com', // D: Email
            '555-9999',    // E: Phone
            '99 Import Lane', // F: Address
            '',            // G: Zone
            '',            // H: Accounting Level
            '',            // I: Job Level
            ServiceTypes::all()[0], // J: Service Types
            $equipment->name, // K: Equipment Type
            LeadJobType::REGULAR->value, // L: Job Type
            '120.00',      // M: Charges ($)
            LeadPaymentMode::CASH->value, // N: Payment Mode
            ClientPaymentStatus::PENDING->value, // O: Payment Status
            ClientCustomerType::DONT_KNOW->value, // P: Customer Type
            '',            // Q: Client Type (skip)
            LeadWeedSpray::NO->value, // R: Weed Spray
            $recurrence->name, // S: Recurrence
            '',            // T: Property Details
            '',            // U: Special Remarks
            '',            // V: Notes
            '',            // W: Latitude
            '',            // X: Longitude
            '',            // Y: Created At
        ]]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonPath('failed', 0)
            ->assertJsonPath('duplicated', 0);

        $this->assertDatabaseHas('clients', ['email' => 'importtest@example.com']);
    }

    public function test_import_creates_jobs_when_create_jobs_flag_is_set(): void
    {
        $recurrence = Recurrence::query()->where('is_active', true)->firstOrFail();
        $equipment = EquipmentType::query()->where('is_active', true)->firstOrFail();

        $file = $this->makeImportXlsx([[
            '',                                    // A: #
            '',                                    // B: Customer ID
            'Job Creator',                         // C: Name
            'jobcreator@example.com',              // D: Email
            '555-1234',                            // E: Phone
            '10 Job Street',                       // F: Address
            '',                                    // G: Zone
            '',                                    // H: Accounting Level
            '',                                    // I: Job Level
            ServiceTypes::all()[0],                // J: Service Types
            $equipment->name,                      // K: Equipment Type
            LeadJobType::REGULAR->value,           // L: Job Type
            '80.00',                               // M: Charges ($)
            LeadPaymentMode::CASH->value,          // N: Payment Mode
            ClientPaymentStatus::PENDING->value,   // O: Payment Status
            ClientCustomerType::DONT_KNOW->value,  // P: Customer Type
            '',                                    // Q: Client Type (skip)
            LeadWeedSpray::NO->value,              // R: Weed Spray
            $recurrence->name,                     // S: Recurrence
            '',                                    // T: Property Details
            '',                                    // U: Special Remarks
            '',                                    // V: Notes
            '',                                    // W: Latitude
            '',                                    // X: Longitude
            '',                                    // Y: Created At
        ]]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file, 'create_jobs' => true])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $client = Client::query()->where('email', 'jobcreator@example.com')->firstOrFail();

        $nextDate = $recurrence->resolve(now());

        if ($nextDate !== null) {
            $this->assertDatabaseHas('jobs', [
                'client_id' => $client->id,
                'scheduled_date' => $nextDate->toDateString(),
                'status' => JobWorkflowStatus::PENDING->value,
                'is_recurring' => true,
            ]);
        } else {
            $this->assertDatabaseMissing('jobs', ['client_id' => $client->id]);
        }
    }

    public function test_import_does_not_create_jobs_when_flag_is_not_set(): void
    {
        $recurrence = Recurrence::query()->where('is_active', true)->firstOrFail();
        $equipment = EquipmentType::query()->where('is_active', true)->firstOrFail();

        $file = $this->makeImportXlsx([[
            '', '', 'No Job Customer', 'nojob@example.com', '', '20 No Job Ave', '', '', '',
            ServiceTypes::all()[0], $equipment->name, LeadJobType::REGULAR->value, '50.00',
            LeadPaymentMode::CASH->value, ClientPaymentStatus::PENDING->value,
            ClientCustomerType::DONT_KNOW->value, '', LeadWeedSpray::NO->value, $recurrence->name,
            '', '', '', '', '', '',
        ]]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 1);

        $client = Client::query()->where('email', 'nojob@example.com')->firstOrFail();
        $this->assertDatabaseMissing('jobs', ['client_id' => $client->id]);
    }

    public function test_import_skips_duplicate_email(): void
    {
        $recurrence = Recurrence::query()->where('is_active', true)->firstOrFail();

        Client::query()->create([
            'name' => 'Existing', 'email' => 'dup@example.com', 'address' => '1 Existing Rd',
            'service_types' => [ServiceTypes::all()[0]], 'weed_spray' => LeadWeedSpray::NO->value,
            'job_type' => LeadJobType::REGULAR->value, 'payment_mode' => LeadPaymentMode::CASH->value,
            'customer_type' => ClientCustomerType::DONT_KNOW->value, 'client_type' => 'Regular',
            'recurrence_id' => $recurrence->id, 'payment_status' => ClientPaymentStatus::PENDING->value,
            'created_by' => $this->admin->id,
        ]);

        $file = $this->makeImportXlsx([[
            '', '', 'Dup Customer', 'dup@example.com', '', '1 Dup Street', '', '', '',
            ServiceTypes::all()[0], '', LeadJobType::REGULAR->value, '', LeadPaymentMode::CASH->value,
            '', ClientCustomerType::DONT_KNOW->value, '', LeadWeedSpray::NO->value, $recurrence->name,
            '', '', '', '', '', '',
        ]]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 0)
            ->assertJsonPath('duplicated', 1);
    }

    public function test_import_reports_validation_failures(): void
    {
        $file = $this->makeImportXlsx([[
            '', '', 'Bad Customer', 'not-an-email', '', '1 Bad Street', '', '', '',
            ServiceTypes::all()[0], '', 'InvalidJobType', '', '', '', '', '', '', '',
            '', '', '', '', '', '',
        ]]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 0)
            ->assertJsonPath('failed', 1);

        $this->assertNotEmpty($response->json('failures'));
    }

    public function test_import_fails_when_address_is_missing(): void
    {
        $recurrence = Recurrence::query()->where('is_active', true)->firstOrFail();

        $file = $this->makeImportXlsx([[
            '', '', 'No Address', 'noaddress@example.com', '', '', '', '', '',
            ServiceTypes::all()[0], '', LeadJobType::REGULAR->value, '', LeadPaymentMode::CASH->value,
            ClientPaymentStatus::PENDING->value, ClientCustomerType::DONT_KNOW->value, '', LeadWeedSpray::NO->value,
            $recurrence->name, '', '', '', '', '', '',
        ]]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 0)
            ->assertJsonPath('failed', 1);

        $this->assertStringContainsString('Address is required', $response->json('failures.0.reason.0'));
    }

    public function test_import_succeeds_without_email(): void
    {
        $recurrence = Recurrence::query()->where('is_active', true)->firstOrFail();
        $equipment = EquipmentType::query()->where('is_active', true)->firstOrFail();

        $file = $this->makeImportXlsx([[
            '', '', 'No Email Customer', '', '', '55 No Email Road', '', '', '',
            ServiceTypes::all()[0], $equipment->name, LeadJobType::REGULAR->value, '60.00',
            LeadPaymentMode::CASH->value, ClientPaymentStatus::PENDING->value,
            ClientCustomerType::DONT_KNOW->value, '', LeadWeedSpray::NO->value, $recurrence->name,
            '', '', '', '', '', '',
        ]]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.clients.import'), ['import_file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonPath('failed', 0);

        $this->assertDatabaseHas('clients', ['address' => '55 No Email Road']);
    }

    /**
     * Build an in-memory xlsx with the expected 4-row preamble followed by $dataRows.
     *
     * @param  array<int, array<int, mixed>>  $dataRows
     */
    private function makeImportXlsx(array $dataRows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Rows 1-3: title / metadata / spacer (content does not matter for import)
        $sheet->setCellValue('A1', 'Customers Import');
        $sheet->setCellValue('A2', 'Generated');
        $sheet->setCellValue('A3', '');

        // Row 4: headers (exactly as defined in ClientsExport::COLUMNS)
        foreach (ClientsExport::COLUMNS as $letter => $colDef) {
            $sheet->setCellValue($letter.'4', $colDef['header']);
        }

        // Row 5+: data
        $letters = array_keys(ClientsExport::COLUMNS);
        foreach ($dataRows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                if (isset($letters[$colIndex])) {
                    $sheet->setCellValue($letters[$colIndex].($rowIndex + 5), $value);
                }
            }
        }

        $path = sys_get_temp_dir().'/test_clients_import_'.uniqid().'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'clients.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validCustomerPayload(): array
    {
        return [
            'name' => 'Gorilla Customer',
            'address' => '100 Customer Way',
            'service_types' => [ServiceTypes::all()[0]],
            'weed_spray' => LeadWeedSpray::NO->value,
            're_completion_days' => LeadReCompletionDays::DAYS_14->value,
            'job_type' => LeadJobType::REGULAR->value,
            'safety_concerns' => ['Pet'],
            'charges' => '99.00',
            'phone' => '555-3000',
            'email' => 'customer@example.com',
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => ClientPaymentStatus::DONE->value,
            'customer_type' => ClientCustomerType::HARD->value,
            'parking_status' => JobParkingStatus::EASY->value,
            'equipment_type_id' => EquipmentType::query()->where('is_active', true)->value('id'),
            'recurrence_id' => Recurrence::query()->where('is_active', true)->value('id'),
            'additional_site_instructions' => 'Use side gate',
            'pet_warning' => 'Dog in backyard',
            'special_remarks' => 'VIP customer',
        ];
    }
}
