<?php

namespace Database\Seeders;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadWeedSpray;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\User;
use App\Models\Zone;
use App\Support\ServiceTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $mowers = User::whereIn('email', [
            'jake.morrison@mowingcrm.test',
            'liam.carter@mowingcrm.test',
        ])->get(['id', 'incentive_percentage']);

        $mowerIds = $mowers->pluck('id')->values();
        $mowerIncentives = $mowers->pluck('incentive_percentage', 'id');

        $adminId = User::where('email', 'admin@mowingcrm.test')->value('id');
        $jobLevelIds = JobLevel::orderBy('sort_order')->pluck('id')->values();
        $zoneIds = Zone::query()->where('is_active', true)->orderBy('sort_order')->pluck('id')->values();
        $serviceTypes = ServiceTypes::all();

        $customers = [
            ['name' => 'Sarah Mitchell', 'email' => 'sarah.mitchell@example.test', 'phone' => '0412 345 678', 'address' => '12 Maple St, Sunnybank QLD 4109'],
            ['name' => 'James O\'Brien', 'email' => 'james.obrien@example.test', 'phone' => '0423 456 789', 'address' => '45 Creek Rd, Capalaba QLD 4157'],
            ['name' => 'Emily Chen', 'email' => 'emily.chen@example.test', 'phone' => '0434 567 890', 'address' => '8 Pacific Ave, Wynnum QLD 4178'],
            ['name' => 'Michael Torres', 'email' => 'michael.torres@example.test', 'phone' => '0445 678 901', 'address' => '22 Hillside Dr, Aspley QLD 4034'],
            ['name' => 'Olivia Brooks', 'email' => 'olivia.brooks@example.test', 'phone' => '0456 789 012', 'address' => '3 Garden Ct, Indooroopilly QLD 4068'],
            ['name' => 'Noah Patel', 'email' => 'noah.patel@example.test', 'phone' => '0467 890 123', 'address' => '91 Coastline Blvd, Redland Bay QLD 4165'],
            ['name' => 'Ava Thompson', 'email' => 'ava.thompson@example.test', 'phone' => '0478 901 234', 'address' => '17 Riverwalk Ln, Kenmore QLD 4069'],
            ['name' => 'Liam Nguyen', 'email' => 'liam.nguyen@example.test', 'phone' => '0489 012 345', 'address' => '56 Ridgeway St, Mount Gravatt QLD 4122'],
            ['name' => 'Sophia Williams', 'email' => 'sophia.williams@example.test', 'phone' => '0490 123 456', 'address' => '4 Harbour View, Manly QLD 4179'],
            ['name' => 'Ethan Clarke', 'email' => 'ethan.clarke@example.test', 'phone' => '0401 234 567', 'address' => '33 Eucalyptus Rd, Springfield QLD 4300'],
            ['name' => 'Isabella Reed', 'email' => 'isabella.reed@example.test', 'phone' => '0411 345 678', 'address' => '19 Orchard St, Paddington QLD 4064'],
            ['name' => 'Mason Hughes', 'email' => 'mason.hughes@example.test', 'phone' => '0422 456 789', 'address' => '70 Station Rd, Ipswich QLD 4305'],
            ['name' => 'Mia Foster', 'email' => 'mia.foster@example.test', 'phone' => '0433 567 890', 'address' => '11 Coral Ave, Robina QLD 4226'],
            ['name' => 'Lucas Bennett', 'email' => 'lucas.bennett@example.test', 'phone' => '0444 678 901', 'address' => '28 Fernvale Dr, Ferny Grove QLD 4055'],
            ['name' => 'Charlotte Adams', 'email' => 'charlotte.adams@example.test', 'phone' => '0455 789 012', 'address' => '9 Lakeside Ct, Carindale QLD 4152'],
            ['name' => 'Henry Scott', 'email' => 'henry.scott@example.test', 'phone' => '0466 890 123', 'address' => '61 Parkview Rd, Wavell Heights QLD 4012'],
            ['name' => 'Amelia Green', 'email' => 'amelia.green@example.test', 'phone' => '0477 901 234', 'address' => '14 Summit Pl, Chapel Hill QLD 4069'],
            ['name' => 'Jack Murphy', 'email' => 'jack.murphy@example.test', 'phone' => '0488 012 345', 'address' => '50 Bayview Tce, Cleveland QLD 4163'],
            ['name' => 'Harper Evans', 'email' => 'harper.evans@example.test', 'phone' => '0499 123 456', 'address' => '2 Acacia Way, Mansfield QLD 4122'],
            ['name' => 'Benjamin King', 'email' => 'benjamin.king@example.test', 'phone' => '0402 234 567', 'address' => '38 Meadowbank St, Mitchelton QLD 4053'],
        ];

        $statuses = [
            JobWorkflowStatus::COMPLETED->value,
            JobWorkflowStatus::COMPLETED->value,
            JobWorkflowStatus::COMPLETED->value,
            JobWorkflowStatus::STARTED->value,
            JobWorkflowStatus::STARTED->value,
            JobWorkflowStatus::HOLD->value,
        ];

        $priorities = ['Low', 'Medium', 'Medium', 'Medium', 'High'];

        $dates = [
            '2026-05-01', '2026-05-03', '2026-05-05', '2026-05-07', '2026-05-08',
            '2026-05-10', '2026-05-12', '2026-05-14', '2026-05-15', '2026-05-17',
            '2026-05-19', '2026-05-21', '2026-05-22', '2026-05-24', '2026-05-26',
            '2026-05-28', '2026-05-29', '2026-06-02', '2026-06-04', '2026-06-06',
        ];

        $times = [
            '07:00', '07:30', '08:00', '08:00', '08:30',
            '09:00', '09:00', '09:30', '10:00', '10:00',
            '10:30', '11:00', '08:00', '08:30', '09:00',
            '09:30', '14:00', '09:30', '08:00', '08:00',
        ];

        $durations = [60, 45, 30, 90, 120, 60, 45, 30, 60, 90, 60, 90, 90, 45, 60, 45, 120, 60, 90, 45];

        $paymentModes = [
            JobOperationalPaymentMode::CASH->value,
            JobOperationalPaymentMode::ONLINE->value,
            JobOperationalPaymentMode::CASH->value,
            JobOperationalPaymentMode::ONLINE->value,
            JobOperationalPaymentMode::BOTH->value,
        ];

        $siteInstructions = [
            'Gate code 1234. Dog on property, keep gate closed at all times.',
            'Leave clippings bagged and place beside green bin.',
            'Access via side gate on east side of property.',
            'Two cats on property. Ensure all gates are latched on exit.',
            'Large property — work systematically from north to south paddock.',
            'Knock on door on arrival. Client likes to inspect before leaving.',
            'No special instructions.',
            'Call client 15 minutes before arrival.',
            'Corner block — mow both street frontages.',
            'Elderly resident. Keep noise to a minimum before 9 AM.',
            'Avoid west side of yard, current renovations in progress.',
            'Coastal property, rinse equipment after use.',
            'Mind pool fence clearance — do not lean equipment on fence.',
            'Standard service. Client is rarely home.',
            'Difficult slope on north side, take extra care.',
            'New client. Leave a service card in letterbox.',
            'Vacant property. Invoice goes to property manager.',
            'Client prefers afternoon visits where possible.',
            'Veggie garden near east fence — do not disturb.',
            'Kids play area in backyard. Tidy up thoroughly before leaving.',
        ];

        $parkingStatuses = [
            JobParkingStatus::EASY->value, JobParkingStatus::NOT_EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::EASY->value,
            JobParkingStatus::NOT_EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::NOT_EASY->value, JobParkingStatus::EASY->value,
            JobParkingStatus::NOT_EASY->value, JobParkingStatus::LONG_AWAY->value, JobParkingStatus::EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::EASY->value,
            JobParkingStatus::NOT_EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::NOT_EASY->value, JobParkingStatus::EASY->value, JobParkingStatus::EASY->value,
        ];

        $customerTypes = JobCustomerType::values();
        $jobTypes = LeadJobType::values();
        $weedSprayOptions = LeadWeedSpray::values();

        foreach ($customers as $i => $customer) {
            $status = $statuses[$i % count($statuses)];
            $isCompleted = $status === JobWorkflowStatus::COMPLETED->value;
            $paymentMode = $paymentModes[$i % count($paymentModes)];
            $paymentStatus = $isCompleted
                ? JobOperationalPaymentStatus::RECEIVED->value
                : JobOperationalPaymentStatus::PENDING->value;

            $assignedMowerId = $mowerIds->isNotEmpty()
                ? $mowerIds[$i % $mowerIds->count()]
                : null;
            $mowerIncentive = $assignedMowerId ? ($mowerIncentives[$assignedMowerId] ?? 0) : 0;

            $job = Job::create([
                'zone_id' => $zoneIds->isNotEmpty() ? $zoneIds[$i % $zoneIds->count()] : null,
                'job_level_id' => $jobLevelIds->isNotEmpty() ? $jobLevelIds[$i % $jobLevelIds->count()] : null,
                'customer_name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'weed_spray' => $weedSprayOptions[$i % count($weedSprayOptions)],
                'job_type' => $jobTypes[$i % count($jobTypes)],
                'client_address' => $customer['address'],
                'latitude' => -27.4 - ($i * 0.01),
                'longitude' => 153.0 + ($i * 0.01),
                'scheduled_date' => $dates[$i],
                'scheduled_time' => $times[$i],
                'estimated_duration_minutes' => $durations[$i],
                'consumed_time_minutes' => $isCompleted ? $durations[$i] + rand(-5, 10) : null,
                'required_services' => [$serviceTypes[$i % count($serviceTypes)]],
                'is_recurring' => true,
                'route_sequence' => $i + 1,
                'priority' => $priorities[$i % count($priorities)],
                'status' => $status,
                'site_instructions' => $siteInstructions[$i],
                'parking_status' => $parkingStatuses[$i],
                'customer_type' => $customerTypes[$i % count($customerTypes)],
                'pet_warning' => null,
                'attached_images' => [],
                'before_images' => [],
                'after_images' => [],
                'done_by_user_id' => $isCompleted ? $assignedMowerId : null,
                'payment_mode' => $paymentMode,
                'payment_status' => $paymentStatus,
                'payment_pending_reason' => null,
                'special_remarks' => null,
                'internal_notes' => null,
                'created_by' => $adminId,
                'charges' => rand(50, 150),
                'incentive_percentage' => $mowerIncentive,
            ]);

            if ($assignedMowerId) {
                DB::table('job_user_assignments')->insert([
                    'job_id' => $job->id,
                    'user_id' => $assignedMowerId,
                    'assignment_date' => $dates[$i],
                    'assignment_status' => $isCompleted ? 'Completed' : 'Assigned',
                    'incentive_percentage' => $mowerIncentive,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
