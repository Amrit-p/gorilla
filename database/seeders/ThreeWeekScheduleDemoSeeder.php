<?php

namespace Database\Seeders;

use App\Enums\FollowupStatus;
use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Models\EquipmentType;
use App\Models\Followup;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Lead;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Support\ServiceTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Demo data for the 3-week dashboard schedule.
 * Dates are relative to "now" so the grid is never empty after seeding.
 *
 * Run: php artisan db:seed --class=ThreeWeekScheduleDemoSeeder
 */
class ThreeWeekScheduleDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@mowingcrm.test')->first();
        $mowers = User::query()
            ->whereIn('email', [
                'jake.morrison@mowingcrm.test',
                'liam.carter@mowingcrm.test',
            ])
            ->get(['id', 'name', 'incentive_percentage']);

        if (! $admin || $mowers->isEmpty()) {
            $this->command?->warn('ThreeWeekScheduleDemoSeeder skipped: run RoleAndPermissionSeeder first.');

            return;
        }

        $zones = Zone::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
        $jobLevelId = JobLevel::query()->where('is_active', true)->orderBy('sort_order')->value('id');
        $equipmentTypeId = EquipmentType::query()->where('is_active', true)->value('id');
        $recurrenceId = Recurrence::query()->where('is_active', true)->value('id');
        $serviceTypes = ServiceTypes::all();

        if ($zones->isEmpty() || empty($serviceTypes)) {
            $this->command?->warn('ThreeWeekScheduleDemoSeeder skipped: seed masters/zones first.');

            return;
        }

        $weekStart = now()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $this->seedJobs($weekStart, $admin, $mowers, $zones, $jobLevelId, $serviceTypes);
        $this->seedPastVisits($admin, $mowers, $zones, $jobLevelId, $serviceTypes);
        $this->seedLeads($weekStart, $admin, $zones, $equipmentTypeId, $recurrenceId, $serviceTypes);
        $this->seedFollowups($weekStart, $admin);

        $this->command?->info('Three-week schedule demo data seeded (jobs, past visits, leads, follow-ups).');
    }

    private function seedJobs(
        Carbon $weekStart,
        User $admin,
        $mowers,
        $zones,
        ?int $jobLevelId,
        array $serviceTypes
    ): void {
        $scenarios = [
            // Week 1 — dense, mix of pending / pay pending / completed
            ['day' => 0, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo Mon Pending'],
            ['day' => 0, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo Mon Paid'],
            ['day' => 1, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo Tue Pay Pending'],
            ['day' => 1, 'status' => JobWorkflowStatus::HOLD, 'payment' => JobOperationalPaymentStatus::PARTIAL, 'name' => 'Demo Tue Hold'],
            ['day' => 2, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo Wed Done'],
            ['day' => 2, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo Wed Pending'],
            ['day' => 3, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo Thu Pending A'],
            ['day' => 3, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PARTIAL, 'name' => 'Demo Thu Partial'],
            ['day' => 3, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo Thu Done'],
            ['day' => 4, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo Fri Pending'],
            ['day' => 5, 'status' => JobWorkflowStatus::HOLD, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo Sat Hold'],
            ['day' => 6, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo Sun Pending'],

            // Week 2
            ['day' => 7, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo W2 Mon'],
            ['day' => 8, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo W2 Tue A'],
            ['day' => 8, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo W2 Tue Done'],
            ['day' => 10, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PARTIAL, 'name' => 'Demo W2 Thu'],
            ['day' => 12, 'status' => JobWorkflowStatus::HOLD, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo W2 Sat Hold'],

            // Week 3
            ['day' => 14, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo W3 Mon'],
            ['day' => 15, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo W3 Tue'],
            ['day' => 17, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'name' => 'Demo W3 Thu Done'],
            ['day' => 19, 'status' => JobWorkflowStatus::PENDING, 'payment' => JobOperationalPaymentStatus::PENDING, 'name' => 'Demo W3 Sat'],
        ];

        foreach ($scenarios as $i => $scenario) {
            $date = $weekStart->copy()->addDays($scenario['day']);
            $mower = $mowers[$i % $mowers->count()];
            $zone = $zones[$i % $zones->count()];
            $isCompleted = $scenario['status'] === JobWorkflowStatus::COMPLETED;
            $marker = 'demo-3wk-'.$date->toDateString().'-'.$i;

            $existing = Job::query()
                ->where('internal_notes', $marker)
                ->first();

            if ($existing) {
                continue;
            }

            $job = Job::query()->create([
                'zone_id' => $zone->id,
                'job_level_id' => $jobLevelId,
                'customer_name' => $scenario['name'],
                'email' => 'demo.job.'.$i.'@example.test',
                'phone' => '04'.str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT),
                'weed_spray' => LeadWeedSpray::NO->value,
                'job_type' => LeadJobType::values()[0] ?? 'Regular',
                'client_address' => (10 + $i).' Demo St, Zone '.$zone->name.' QLD 4000',
                'latitude' => -27.45 - ($i * 0.002),
                'longitude' => 153.02 + ($i * 0.002),
                'scheduled_date' => $date->toDateString(),
                'scheduled_time' => sprintf('%02d:00', 7 + ($i % 6)),
                'estimated_duration_minutes' => 45 + (($i % 3) * 15),
                'consumed_time_minutes' => $isCompleted ? 50 + ($i % 20) : null,
                'required_services' => [$serviceTypes[$i % count($serviceTypes)]],
                'is_recurring' => false,
                'route_sequence' => $i + 1,
                'priority' => 'Medium',
                'status' => $scenario['status']->value,
                'site_instructions' => 'Demo seed job for 3-week schedule.',
                'parking_status' => JobParkingStatus::EASY->value,
                'customer_type' => JobCustomerType::values()[0] ?? 'Easy',
                'attached_images' => [],
                'before_images' => [],
                'after_images' => [],
                'done_by_user_id' => $isCompleted ? $mower->id : $mower->id,
                'payment_mode' => $i % 2 === 0
                    ? JobOperationalPaymentMode::CASH->value
                    : JobOperationalPaymentMode::ONLINE->value,
                'payment_status' => $scenario['payment']->value,
                'payment_pending_reason' => $scenario['payment'] === JobOperationalPaymentStatus::PENDING
                    ? 'Awaiting customer payment (demo)'
                    : null,
                'charges' => 80 + (($i % 5) * 25),
                'incentive_percentage' => (float) ($mower->incentive_percentage ?? 5),
                'internal_notes' => $marker,
                'created_by' => $admin->id,
            ]);

            DB::table('job_user_assignments')->insertOrIgnore([
                'job_id' => $job->id,
                'user_id' => $mower->id,
                'assignment_date' => $date->toDateString(),
                'assignment_status' => $isCompleted ? 'Completed' : 'Assigned',
                'incentive_percentage' => (float) ($mower->incentive_percentage ?? 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Past visits for Customer Details modal — matched by same phone/email.
     * Without these, "Past visits" is empty because each schedule job had a unique phone.
     */
    private function seedPastVisits(
        User $admin,
        $mowers,
        $zones,
        ?int $jobLevelId,
        array $serviceTypes
    ): void {
        $anchorNames = [
            'Demo W3 Sat',
            'Demo Wed Done',
            'Demo Mon Pending',
            'Demo Thu Done',
            'Demo W2 Tue Done',
            'Demo Fri Pending',
            'Demo W2 Mon',
        ];

        $anchors = Job::query()
            ->where('internal_notes', 'like', 'demo-3wk-%')
            ->whereIn('customer_name', $anchorNames)
            ->get();

        if ($anchors->isEmpty()) {
            $this->command?->warn('seedPastVisits: no demo schedule jobs found to attach history to.');

            return;
        }

        $pastTemplates = [
            ['weeks_ago' => 3, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'charges' => 75],
            ['weeks_ago' => 6, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'charges' => 80],
            ['weeks_ago' => 9, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::PENDING, 'charges' => 85],
            ['weeks_ago' => 12, 'status' => JobWorkflowStatus::COMPLETED, 'payment' => JobOperationalPaymentStatus::RECEIVED, 'charges' => 70],
            ['weeks_ago' => 16, 'status' => JobWorkflowStatus::HOLD, 'payment' => JobOperationalPaymentStatus::PARTIAL, 'charges' => 90],
        ];

        foreach ($anchors as $anchor) {
            $phone = trim((string) $anchor->phone);
            $email = trim((string) $anchor->email);

            if ($phone === '' && $email === '') {
                continue;
            }

            foreach ($pastTemplates as $n => $past) {
                $marker = 'demo-past-'.$anchor->id.'-'.$n;
                if (Job::query()->where('internal_notes', $marker)->exists()) {
                    continue;
                }

                $date = now()->subWeeks($past['weeks_ago'])->startOfWeek(Carbon::MONDAY)->addDays($n % 5);
                $mower = $mowers[$n % $mowers->count()];
                $isCompleted = $past['status'] === JobWorkflowStatus::COMPLETED;

                $job = Job::query()->create([
                    'zone_id' => $anchor->zone_id ?: $zones->first()?->id,
                    'job_level_id' => $jobLevelId,
                    'customer_name' => $anchor->customer_name,
                    'email' => $email !== '' ? $email : null,
                    'phone' => $phone !== '' ? $phone : null,
                    'weed_spray' => LeadWeedSpray::NO->value,
                    'job_type' => $anchor->job_type ?: (LeadJobType::values()[0] ?? 'Regular'),
                    'client_address' => $anchor->client_address,
                    'latitude' => $anchor->latitude,
                    'longitude' => $anchor->longitude,
                    'scheduled_date' => $date->toDateString(),
                    'scheduled_time' => sprintf('%02d:30', 8 + ($n % 4)),
                    'estimated_duration_minutes' => 60,
                    'consumed_time_minutes' => $isCompleted ? 55 + $n : null,
                    'required_services' => $anchor->required_services ?: [$serviceTypes[$n % count($serviceTypes)]],
                    'is_recurring' => true,
                    'route_sequence' => $n + 1,
                    'priority' => 'Medium',
                    'status' => $past['status']->value,
                    'site_instructions' => 'Past visit (demo) for customer history.',
                    'parking_status' => JobParkingStatus::EASY->value,
                    'customer_type' => JobCustomerType::values()[0] ?? 'Easy',
                    'attached_images' => [],
                    'before_images' => [],
                    'after_images' => [],
                    'done_by_user_id' => $mower->id,
                    'payment_mode' => $n % 2 === 0
                        ? JobOperationalPaymentMode::CASH->value
                        : JobOperationalPaymentMode::ONLINE->value,
                    'payment_status' => $past['payment']->value,
                    'payment_pending_reason' => $past['payment'] === JobOperationalPaymentStatus::PENDING
                        ? 'Legacy unpaid visit (demo)'
                        : null,
                    'charges' => $past['charges'],
                    'incentive_percentage' => (float) ($mower->incentive_percentage ?? 5),
                    'internal_notes' => $marker,
                    'created_by' => $admin->id,
                ]);

                DB::table('job_user_assignments')->insertOrIgnore([
                    'job_id' => $job->id,
                    'user_id' => $mower->id,
                    'assignment_date' => $date->toDateString(),
                    'assignment_status' => $isCompleted ? 'Completed' : 'Assigned',
                    'incentive_percentage' => (float) ($mower->incentive_percentage ?? 5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedLeads(
        Carbon $weekStart,
        User $admin,
        $zones,
        ?int $equipmentTypeId,
        ?int $recurrenceId,
        array $serviceTypes
    ): void {
        $leadScenarios = [
            ['day' => 1, 'status' => LeadStatus::NEW, 'name' => 'Demo Lead New'],
            ['day' => 1, 'status' => LeadStatus::FOLLOW_UP, 'name' => 'Demo Lead Follow Up A'],
            ['day' => 2, 'status' => LeadStatus::FOLLOW_UP, 'name' => 'Demo Lead Follow Up B'],
            ['day' => 3, 'status' => LeadStatus::NEW, 'name' => 'Demo Lead Midweek'],
            ['day' => 8, 'status' => LeadStatus::FOLLOW_UP, 'name' => 'Demo Lead W2 Follow Up'],
            ['day' => 9, 'status' => LeadStatus::NEW, 'name' => 'Demo Lead W2 New'],
            ['day' => 15, 'status' => LeadStatus::FOLLOW_UP, 'name' => 'Demo Lead W3 Follow Up'],
        ];

        foreach ($leadScenarios as $i => $scenario) {
            $date = $weekStart->copy()->addDays($scenario['day']);
            $zone = $zones[$i % $zones->count()];
            $email = 'demo.lead.'.$i.'@example.test';

            if (Lead::query()->where('email', $email)->exists()) {
                continue;
            }

            Lead::query()->create([
                'zone_id' => $zone->id,
                'client_name' => $scenario['name'],
                'email' => $email,
                'mobile_number' => '04'.str_pad((string) (20000000 + $i), 8, '0', STR_PAD_LEFT),
                'address' => (20 + $i).' Prospect Ave, '.$zone->name.' QLD 4100',
                'service_types' => [$serviceTypes[$i % count($serviceTypes)]],
                'weed_spray' => LeadWeedSpray::NO->value,
                'equipment_type_id' => $equipmentTypeId,
                'recurrence_id' => $recurrenceId,
                'job_type' => LeadJobType::values()[0] ?? 'Regular',
                'charges' => 95 + ($i * 10),
                'payment_mode' => LeadPaymentMode::values()[0] ?? 'Cash',
                'payment_status' => LeadPaymentStatus::PENDING->value,
                'remarks' => 'Demo lead for 3-week schedule.',
                'lead_date' => $date->toDateString(),
                'lead_time' => '09:00',
                'status' => $scenario['status']->value,
                'is_locked' => false,
                'queued_for_scheduling' => false,
                'converted_at' => null,
                'assigned_sales_user_id' => $admin->id,
            ]);
        }
    }

    private function seedFollowups(Carbon $weekStart, User $admin): void
    {
        $jobs = Job::query()
            ->where('internal_notes', 'like', 'demo-3wk-%')
            ->orderBy('id')
            ->limit(5)
            ->get(['id', 'scheduled_date']);

        $leads = Lead::query()
            ->where('email', 'like', 'demo.lead.%@example.test')
            ->orderBy('id')
            ->limit(4)
            ->get(['id', 'lead_date']);

        $days = [0, 1, 2, 8, 15];

        foreach ($jobs as $i => $job) {
            $due = $weekStart->copy()->addDays($days[$i % count($days)])->setTime(10, 0);
            $outcome = 'Demo follow-up for job #'.$job->id;

            if (Followup::query()->where('outcome', $outcome)->exists()) {
                continue;
            }

            Followup::query()->create([
                'followable_type' => Job::class,
                'followable_id' => $job->id,
                'created_by' => $admin->id,
                'outcome' => $outcome,
                'notes' => 'Seeded for dashboard Follow-up counts.',
                'status' => FollowupStatus::Pending->value,
                'next_followup_at' => $due,
            ]);
        }

        foreach ($leads as $i => $lead) {
            $due = $weekStart->copy()->addDays($days[($i + 1) % count($days)])->setTime(14, 30);
            $outcome = 'Demo follow-up for lead #'.$lead->id;

            if (Followup::query()->where('outcome', $outcome)->exists()) {
                continue;
            }

            Followup::query()->create([
                'followable_type' => Lead::class,
                'followable_id' => $lead->id,
                'created_by' => $admin->id,
                'outcome' => $outcome,
                'notes' => 'Seeded for dashboard Follow-up counts.',
                'status' => FollowupStatus::Pending->value,
                'next_followup_at' => $due,
            ]);
        }
    }
}
