<?php

namespace Tests\Feature;

use App\Enums\FollowupStatus;
use App\Models\Followup;
use App\Models\User;
use App\Notifications\FollowUpDue;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendFollowUpRemindersTest extends TestCase
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

    public function test_reminder_goes_to_the_assigned_user(): void
    {
        Notification::fake();

        Followup::query()->create([
            'title' => 'Chase the supplier',
            'created_by' => $this->admin->id,
            'assigned_to' => $this->mower->id,
            'notes' => 'Parts were promised for today.',
            'status' => FollowupStatus::Pending->value,
            'next_followup_at' => today(),
        ]);

        $this->artisan('followups:notify')->assertSuccessful();

        Notification::assertSentTo($this->mower, FollowUpDue::class);
        Notification::assertNotSentTo($this->admin, FollowUpDue::class);
    }

    public function test_reminder_falls_back_to_the_creator_when_unassigned(): void
    {
        Notification::fake();

        Followup::query()->create([
            'title' => 'Unassigned reminder',
            'created_by' => $this->admin->id,
            'notes' => 'Nobody owns this one yet.',
            'status' => FollowupStatus::Pending->value,
            'next_followup_at' => today(),
        ]);

        $this->artisan('followups:notify')->assertSuccessful();

        Notification::assertSentTo($this->admin, FollowUpDue::class);
    }

    public function test_general_followup_reminder_uses_its_title(): void
    {
        $followup = Followup::query()->create([
            'title' => 'Renew the trailer registration',
            'created_by' => $this->admin->id,
            'notes' => 'Due at the end of the month.',
            'status' => FollowupStatus::Pending->value,
            'next_followup_at' => today(),
        ]);

        $mail = (new FollowUpDue($followup))->toMail($this->admin);

        $this->assertSame('Follow-up due: Renew the trailer registration', $mail->subject);
    }
}
