<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Contractor;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractorsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mower;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
        $this->mower = User::query()->where('email', 'jake.morrison@mowingcrm.test')->firstOrFail();
    }

    // ── Access control ───────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_contractors_index(): void
    {
        $this->get(route('admin.contractors.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_contractors_index(): void
    {
        $this->actingAs($this->mower)
            ->get(route('admin.contractors.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_contractors_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.contractors.index'))
            ->assertOk()
            ->assertSee('Contractors');
    }

    // ── Contractor CRUD ──────────────────────────────────────────────────────

    public function test_admin_can_create_contractor(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.store'), [
                'name' => 'Acme Corp',
                'phone' => '0400000001',
                'email' => 'acme@example.com',
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Contractor created successfully.']);

        $this->assertDatabaseHas('contractors', ['name' => 'Acme Corp', 'email' => 'acme@example.com']);
    }

    public function test_phone_is_required_to_create_contractor(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.store'), [
                'name' => 'Acme Corp',
                'email' => 'acme@example.com',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_email_is_optional_for_contractor(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.store'), [
                'name' => 'No Email Co',
                'phone' => '0400000002',
            ])
            ->assertOk();

        $this->assertDatabaseHas('contractors', ['name' => 'No Email Co', 'email' => null]);
    }

    public function test_duplicate_email_is_rejected_on_store(): void
    {
        Contractor::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.store'), [
                'name' => 'Other Corp',
                'phone' => '0400000003',
                'email' => 'taken@example.com',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_update_contractor(): void
    {
        $contractor = Contractor::factory()->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.contractors.update', $contractor), [
                'name' => 'Updated Name',
                'phone' => '0411111111',
                'email' => null,
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Contractor updated successfully.']);

        $this->assertDatabaseHas('contractors', ['id' => $contractor->id, 'name' => 'Updated Name']);
    }

    public function test_duplicate_email_is_rejected_on_update_for_another_contractor(): void
    {
        $existing = Contractor::factory()->create(['email' => 'existing@example.com']);
        $other = Contractor::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.contractors.update', $other), [
                'name' => $other->name,
                'phone' => $other->phone,
                'email' => 'existing@example.com',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_view_contractor_show_json(): void
    {
        $contractor = Contractor::factory()->create();

        $this->actingAs($this->admin)
            ->getJson(route('admin.contractors.show', $contractor))
            ->assertOk()
            ->assertJsonFragment(['id' => $contractor->id, 'name' => $contractor->name]);
    }

    // ── Contract CRUD ────────────────────────────────────────────────────────

    public function test_admin_can_create_contract(): void
    {
        $contractor = Contractor::factory()->create();

        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.contracts.store', $contractor), [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Contract created successfully.']);

        $this->assertDatabaseHas('contracts', [
            'contractor_id' => $contractor->id,
            'status' => ContractStatus::Active->value,
        ]);
    }

    public function test_creating_contract_records_initial_status_history(): void
    {
        $contractor = Contractor::factory()->create();

        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.contracts.store', $contractor), [
                'start_date' => '2026-01-01',
            ])
            ->assertOk();

        $contract = Contract::query()->where('contractor_id', $contractor->id)->firstOrFail();

        $this->assertDatabaseHas('contract_status_histories', [
            'contract_id' => $contract->id,
            'status' => ContractStatus::Active->value,
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_end_date_must_be_after_or_equal_to_start_date(): void
    {
        $contractor = Contractor::factory()->create();

        $this->actingAs($this->admin)
            ->postJson(route('admin.contractors.contracts.store', $contractor), [
                'start_date' => '2026-06-01',
                'end_date' => '2026-05-01',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_admin_can_update_contract_dates(): void
    {
        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create(['contractor_id' => $contractor->id]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.contractors.contracts.update', [$contractor, $contract]), [
                'start_date' => '2026-03-01',
                'end_date' => '2027-03-01',
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Contract updated successfully.']);

        $this->assertEquals(
            '2026-03-01',
            Contract::query()->find($contract->id)->start_date->format('Y-m-d')
        );
    }

    // ── Contract status ──────────────────────────────────────────────────────

    public function test_admin_can_toggle_contract_status(): void
    {
        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'status' => ContractStatus::Active->value,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.contractors.contracts.status.update', [$contractor, $contract]), [
                'status' => ContractStatus::Inactive->value,
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Contract status updated successfully.']);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'status' => ContractStatus::Inactive->value,
        ]);
    }

    public function test_toggling_contract_status_inserts_history_row(): void
    {
        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'status' => ContractStatus::Active->value,
        ]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.contractors.contracts.status.update', [$contractor, $contract]), [
                'status' => ContractStatus::Inactive->value,
            ])
            ->assertOk();

        $this->assertDatabaseHas('contract_status_histories', [
            'contract_id' => $contract->id,
            'status' => ContractStatus::Inactive->value,
            'changed_by' => $this->admin->id,
        ]);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create(['contractor_id' => $contractor->id]);

        $this->actingAs($this->admin)
            ->patchJson(route('admin.contractors.contracts.status.update', [$contractor, $contract]), [
                'status' => 'pending',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    // ── Document upload / delete ─────────────────────────────────────────────

    public function test_admin_can_upload_contract_document(): void
    {
        Storage::fake('public');

        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create(['contractor_id' => $contractor->id]);

        $this->actingAs($this->admin)
            ->postJson(
                route('admin.contractors.contracts.documents.store', [$contractor, $contract]),
                ['document' => UploadedFile::fake()->create('contract.pdf', 200, 'application/pdf')]
            )
            ->assertOk()
            ->assertJsonFragment(['message' => 'Document uploaded successfully.']);

        $this->assertDatabaseHas('contract_documents', [
            'contract_id' => $contract->id,
            'original_name' => 'contract.pdf',
            'file_type' => 'pdf',
        ]);
    }

    public function test_invalid_file_type_is_rejected_on_upload(): void
    {
        Storage::fake('public');

        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create(['contractor_id' => $contractor->id]);

        $this->actingAs($this->admin)
            ->postJson(
                route('admin.contractors.contracts.documents.store', [$contractor, $contract]),
                ['document' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream')]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['document']);
    }

    public function test_admin_can_delete_contract_document(): void
    {
        Storage::fake('public');

        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create(['contractor_id' => $contractor->id]);
        $document = ContractDocument::factory()->create([
            'contract_id' => $contract->id,
            'file_path' => 'contract-documents/1/test.pdf',
        ]);

        $this->actingAs($this->admin)
            ->deleteJson(
                route('admin.contractors.contracts.documents.destroy', [$contractor, $contract, $document])
            )
            ->assertOk()
            ->assertJsonFragment(['message' => 'Document deleted successfully.']);

        $this->assertDatabaseMissing('contract_documents', ['id' => $document->id]);
    }

    // ── Search / filtering ───────────────────────────────────────────────────

    public function test_index_search_filters_by_name(): void
    {
        Contractor::factory()->create(['name' => 'Alpha Corp', 'phone' => '0411000001']);
        Contractor::factory()->create(['name' => 'Beta Corp', 'phone' => '0411000002']);

        $this->actingAs($this->admin)
            ->get(route('admin.contractors.index', ['search' => 'Alpha']))
            ->assertOk()
            ->assertSee('Alpha Corp')
            ->assertDontSee('Beta Corp');
    }
}
