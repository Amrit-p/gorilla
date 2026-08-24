<?php

namespace Tests\Feature;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadStatus;
use App\Enums\UserEfficiency;
use App\Imports\GorillaCrmBackupImport;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Support\CrmRoles;
use Database\Seeders\AccountingLevelSeeder;
use Database\Seeders\MasterCatalogSeeder;
use Database\Seeders\RecurrenceSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\ZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrmBackupImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ZoneSeeder::class);
        $this->seed(MasterCatalogSeeder::class);
        $this->seed(RecurrenceSeeder::class);
        $this->seed(AccountingLevelSeeder::class);

        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_office_manager_can_open_the_hidden_import_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.tools.crm-import.index'))
            ->assertOk()
            ->assertSee('Import Gorilla CRM backup');
    }

    public function test_non_office_manager_cannot_open_the_import_page(): void
    {
        $mower = User::query()->where('email', 'jake.morrison@mowingcrm.test')->firstOrFail();

        $this->actingAs($mower)
            ->get(route('admin.tools.crm-import.index'))
            ->assertForbidden();
    }

    public function test_the_import_page_is_not_linked_from_the_sidebar(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertDontSee(route('admin.tools.crm-import.index'));
    }

    public function test_upload_seeds_users_jobs_and_leads_and_reports_the_file_hash(): void
    {
        $json = $this->backupJson();

        $this->actingAs($this->admin)
            ->post(route('admin.tools.crm-import.store'), [
                'backup_file' => $this->backupFile($json),
                'mower_email_domain' => 'gorillamowing.co.nz',
            ])
            ->assertRedirect()
            ->assertSessionHas('import_result');

        $this->followRedirects(
            $this->actingAs($this->admin)->get(route('admin.tools.crm-import.index'))
        );

        $mower = User::query()->where('name', 'Harpreet Singh')->firstOrFail();
        $this->assertSame('harpreet.singh@gorillamowing.co.nz', $mower->email);
        $this->assertTrue($mower->hasRole(CrmRoles::MOWER));
        $this->assertTrue(Hash::check(GorillaCrmBackupImport::DEFAULT_PASSWORD, $mower->password));
        $this->assertSame(UserEfficiency::GOOD->value, $mower->efficiency);

        $this->assertSame(
            UserEfficiency::BEGINNER->value,
            User::query()->where('name', 'Sukhjeet Kumar')->value('efficiency')
        );

        // One upcoming job plus one job per completed visit in detailedHistory.
        $this->assertSame(2, Job::query()->where('client_address', '83 Maplesden Drive')->count());

        $upcoming = Job::query()
            ->where('client_address', '83 Maplesden Drive')
            ->where('scheduled_date', '2026-09-03')
            ->firstOrFail();

        // The export has no real contact names, so the address doubles as the customer name.
        $this->assertSame('83 Maplesden Drive', $upcoming->customer_name);
        $this->assertSame(JobWorkflowStatus::PENDING->value, $upcoming->status);
        $this->assertSame('Bi-Weekly', $upcoming->recurrence->name);
        $this->assertSame('Zone 2', $upcoming->zone->name);
        $this->assertSame('⭐ C', $upcoming->accountingLevel->name);
        $this->assertSame('Platinum', $upcoming->clientRating->name);
        $this->assertSame(['Mulching', 'Side Shoot'], $upcoming->required_services);
        $this->assertSame($mower->id, $upcoming->done_by_user_id);

        $history = Job::query()
            ->where('client_address', '83 Maplesden Drive')
            ->where('scheduled_date', '2026-07-22')
            ->firstOrFail();

        $this->assertSame(JobWorkflowStatus::COMPLETED->value, $history->status);
        $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $history->payment_status);
        $this->assertSame('92.00', $history->charges);
        $this->assertDatabaseHas('job_user_assignments', [
            'job_id' => $history->id,
            'user_id' => $mower->id,
            'assignment_status' => JobWorkflowStatus::COMPLETED->value,
        ]);

        $lead = Lead::query()->where('address', '25 Secoia Crescent')->firstOrFail();
        $this->assertSame(LeadStatus::NEW->value, $lead->status);
        $this->assertSame('Mangere Property', $lead->client_name);
        $this->assertSame(['Mulching'], $lead->service_types);
    }

    public function test_mismatched_verified_amounts_are_noted_without_becoming_partial_payments(): void
    {
        $json = json_encode([
            'timestamp' => '2026-08-21T11:51:45.254Z',
            'mowers' => [],
            'crm_data' => [[
                'id' => '7VpB86zNC0vYCenPnaoc',
                'type' => 'Customer',
                'name' => 'No Name',
                'address' => '169B Preston Road Otara Auckland',
                'zone' => 'Zone 1',
                'status' => 'Active',
                'jobType' => 'Regular',
                'frequency' => '2-Weekly',
                'charges' => 75,
                'paymentMode' => 'Cash',
                'services' => ['Mulching'],
                'date' => '2026-08-25',
                'detailedHistory' => [
                    [
                        'date' => '2026-07-15',
                        'amount' => 75,
                        'verifiedAmount' => 80,
                        'receivedDate' => '2026-07-15',
                        'paymentStatus' => 'Paid',
                        'status' => 'Received',
                        'paymentVerified' => true,
                        'remarks' => 'Imported from Excel',
                        'mower' => 'Labhpreet Singh',
                    ],
                    [
                        'date' => '2026-08-11',
                        'amount' => 75,
                        'verifiedAmount' => 70,
                        'receivedDate' => '2026-08-12',
                        'paymentStatus' => 'Paid',
                        'mower' => 'Labhpreet Singh',
                    ],
                    [
                        'date' => '2026-06-17',
                        'amount' => 75,
                        'verifiedAmount' => 75,
                        'paymentStatus' => 'Paid',
                        'mower' => 'Labhpreet Singh',
                    ],
                ],
            ]],
        ], JSON_THROW_ON_ERROR);

        $this->actingAs($this->admin)->post(route('admin.tools.crm-import.store'), [
            'backup_file' => $this->backupFile($json),
            'mower_email_domain' => 'gorillamowing.co.nz',
        ]);

        $over = Job::query()->where('scheduled_date', '2026-07-15')->firstOrFail();
        $under = Job::query()->where('scheduled_date', '2026-08-11')->firstOrFail();
        $exact = Job::query()->where('scheduled_date', '2026-06-17')->firstOrFail();

        // A settled payment stays Received; charges keep the billed figure.
        foreach ([$over, $under, $exact] as $job) {
            $this->assertSame(JobOperationalPaymentStatus::RECEIVED->value, $job->payment_status);
            $this->assertSame('75.00', $job->charges);
            $this->assertNull($job->first_payment);
            $this->assertNull($job->second_payment);
        }

        $this->assertStringContainsString('Verified amount 80.00', $over->internal_notes);
        $this->assertStringContainsString('over by 5.00', $over->internal_notes);
        $this->assertStringContainsString('short by 5.00', $under->internal_notes);
        $this->assertStringNotContainsString('Verified amount', (string) $exact->internal_notes);

        // verifiedAmount implies the office reconciled it, even without paymentVerified.
        $this->assertNotNull($under->verified_at);
        $this->assertSame('2026-08-12', $under->verified_at->toDateString());
    }

    public function test_result_summary_shows_the_sha256_of_the_uploaded_file(): void
    {
        $json = $this->backupJson();
        $expected = hash('sha256', $json);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.tools.crm-import.store'), [
                'backup_file' => $this->backupFile($json),
                'mower_email_domain' => 'gorillamowing.co.nz',
            ]);

        $response->assertSessionHas('import_result.sha256', $expected);

        $this->actingAs($this->admin)
            ->get(route('admin.tools.crm-import.index'))
            ->assertOk()
            ->assertSee($expected);
    }

    public function test_dry_run_reports_counts_without_writing_anything(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.tools.crm-import.store'), [
                'backup_file' => $this->backupFile($this->backupJson()),
                'mower_email_domain' => 'gorillamowing.co.nz',
                'dry_run' => '1',
            ]);

        $response->assertSessionHas('import_result.dry_run', true);
        $response->assertSessionHas('import_result.counts.jobs_created', 1);
        $response->assertSessionHas('import_result.counts.history_jobs_created', 1);

        $this->assertSame(0, Job::query()->count());
        $this->assertSame(0, Lead::query()->count());
        $this->assertNull(User::query()->where('name', 'Harpreet Singh')->first());
    }

    public function test_re_importing_the_same_backup_skips_existing_records(): void
    {
        foreach (range(1, 2) as $ignored) {
            $this->actingAs($this->admin)->post(route('admin.tools.crm-import.store'), [
                'backup_file' => $this->backupFile($this->backupJson()),
                'mower_email_domain' => 'gorillamowing.co.nz',
            ]);
        }

        $this->assertSame(2, Job::query()->count());
        $this->assertSame(1, Lead::query()->count());
        $this->assertSame(1, User::query()->where('name', 'Harpreet Singh')->count());
    }

    public function test_invalid_json_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tools.crm-import.store'), [
                'backup_file' => UploadedFile::fake()->createWithContent('backup.json', '{not json'),
                'mower_email_domain' => 'gorillamowing.co.nz',
            ])
            ->assertSessionHasErrors('backup_file');

        $this->assertSame(0, Job::query()->count());
    }

    public function test_non_json_uploads_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tools.crm-import.store'), [
                'backup_file' => UploadedFile::fake()->create('backup.csv', 4),
                'mower_email_domain' => 'gorillamowing.co.nz',
            ])
            ->assertSessionHasErrors('backup_file');
    }

    private function backupFile(string $json): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('Gorilla_CRM_Backup.json', $json);
    }

    private function backupJson(): string
    {
        return json_encode([
            'timestamp' => '2026-08-21T11:51:45.254Z',
            'mowers' => [
                [
                    'id' => 'd5v7bzCNS1RngfVCUmmE',
                    'name' => 'Harpreet Singh',
                    'phone' => '+64224531202',
                    'efficiency' => '95',
                    'createdAt' => ['seconds' => 1783001943, 'nanoseconds' => 824000000],
                ],
                [
                    'id' => 'g9b2tCCc1oL2VHg4p6Qt',
                    'name' => 'Sukhjeet Kumar',
                    'phone' => '',
                    'efficiency' => '70',
                    'createdAt' => ['seconds' => 1783664718, 'nanoseconds' => 343000000],
                ],
            ],
            'crm_data' => [
                [
                    'id' => '07feMLH8stWbqyQ8Wi3K',
                    'type' => 'Customer',
                    'name' => 'No Name',
                    'address' => '83 Maplesden Drive',
                    'zone' => 'Zone 2',
                    'status' => 'Active',
                    'jobType' => 'Regular',
                    'frequency' => '2',
                    'charges' => '92',
                    'paymentMode' => 'Online',
                    'accountingLevel' => '*C',
                    'rating' => 'Platinum Client',
                    'assignedEquipment' => 'Mulcher',
                    'weedSpray' => 'No',
                    'customerType' => 'Medium',
                    'services' => ['Mulching', 'Sideshoot'],
                    'lat' => '-37.0295883',
                    'lng' => '174.8648772',
                    'date' => '2026-09-03',
                    'Done By' => 'HARPREET SINGH',
                    'jobHistory' => ['2026-07-22'],
                    'detailedHistory' => [
                        [
                            'date' => '2026-07-22',
                            'amount' => 92,
                            'mower' => 'Harpreet Singh',
                            'status' => 'Received',
                            'mode' => 'Imported',
                            'paymentVerified' => true,
                            'remarks' => 'Imported from Excel',
                        ],
                    ],
                    'createdAt' => ['seconds' => 1785118522, 'nanoseconds' => 513000000],
                    'updatedAt' => ['seconds' => 1787237636, 'nanoseconds' => 288000000],
                ],
                [
                    'id' => '4vBh3TaoEW3Mt2hu4sJS',
                    'type' => 'Lead',
                    'name' => 'Mangere Property',
                    'address' => '25 Secoia Crescent',
                    'zone' => 'Zone 2',
                    'status' => 'Active',
                    'jobType' => 'Regular',
                    'frequency' => '3',
                    'charges' => '40',
                    'paymentMode' => 'Online',
                    'serviceType' => 'Mulching',
                    'phone' => '+64274275990',
                    'weedSpray' => 'No',
                    'lat' => -36.9789609,
                    'lng' => 174.8040906,
                    'date' => '2026-09-04',
                    'detailedHistory' => [
                        ['date' => '2026-08-14', 'amount' => 40, 'mower' => 'Labhpreet Singh', 'paymentStatus' => 'Paid'],
                    ],
                    'createdAt' => ['seconds' => 1786006130, 'nanoseconds' => 301000000],
                    'updatedAt' => ['seconds' => 1786706478, 'nanoseconds' => 359000000],
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }
}
