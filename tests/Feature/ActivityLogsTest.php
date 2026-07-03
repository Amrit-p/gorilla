<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->admin = User::query()->where('email', 'admin@mowingcrm.test')->firstOrFail();
    }

    public function test_admin_can_access_activity_logs_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Audit Timeline');
    }

    public function test_index_filters_by_date_range(): void
    {
        $inRange = ActivityLog::query()->create([
            'user_id' => $this->admin->id,
            'action' => 'login',
            'description' => 'In range log',
        ]);
        $inRange->forceFill(['created_at' => '2026-01-10 10:00:00'])->save();

        $outOfRange = ActivityLog::query()->create([
            'user_id' => $this->admin->id,
            'action' => 'login',
            'description' => 'Out of range log',
        ]);
        $outOfRange->forceFill(['created_at' => '2026-03-10 10:00:00'])->save();

        $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'date_range' => ['start' => '2026-01-01', 'end' => '2026-01-31'],
            ]))
            ->assertOk()
            ->assertSee('In range log')
            ->assertDontSee('Out of range log');

        $this->assertNotNull($inRange->id);
        $this->assertNotNull($outOfRange->id);
    }

    public function test_ajax_request_returns_partial_html(): void
    {
        ActivityLog::query()->create([
            'user_id' => $this->admin->id,
            'action' => 'login',
            'description' => 'Ajax log entry',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertStringContainsString('Ajax log entry', $response->json('html'));
    }
}
