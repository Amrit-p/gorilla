<?php

namespace Database\Seeders;

use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\Client;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobSeeder extends Seeder
{
    public function run(): void
    {
        $mowerIds = User::whereIn('email', [
            'jake.morrison@mowingcrm.test',
            'liam.carter@mowingcrm.test',
        ])->pluck('id')->values();

        $adminId   = User::where('email', 'admin@mowingcrm.test')->value('id');
        $clients   = Client::with('lead')->orderBy('id')->take(20)->get();

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
            'Driveway', 'Street', 'Driveway', 'Driveway', 'Driveway',
            'Street', 'Driveway', 'Driveway', 'Street', 'Driveway',
            'Street', 'Street', 'Driveway', 'Driveway', 'Driveway',
            'Street', 'Driveway', 'Street', 'Driveway', 'Driveway',
        ];

        foreach ($clients as $i => $client) {
            $status = $statuses[$i % count($statuses)];
            $isCompleted = $status === JobWorkflowStatus::COMPLETED->value;
            $paymentMode = $paymentModes[$i % count($paymentModes)];
            $paymentStatus = $isCompleted
                ? JobOperationalPaymentStatus::RECEIVED->value
                : JobOperationalPaymentStatus::PENDING->value;

            $assignedMowerId = $mowerIds[$i % $mowerIds->count()];

            $job = Job::create([
                'client_id'                  => $client->id,
                'lead_id'                    => $client->lead_id,
                'zone_id'                    => $client->zone_id,
                'equipment_type_id'          => $client->equipment_type_id,
                'recurrence_id'              => $client->recurrence_id,
                'client_address'             => $client->address,
                'latitude'                   => $client->latitude,
                'longitude'                  => $client->longitude,
                'scheduled_date'             => $dates[$i],
                'scheduled_time'             => $times[$i],
                'estimated_duration_minutes' => $durations[$i],
                'consumed_time_minutes'      => $isCompleted ? $durations[$i] + rand(-5, 10) : null,
                'required_services'          => $client->service_types,
                'is_recurring'               => true,
                'route_sequence'             => $i + 1,
                'priority'                   => $priorities[$i % count($priorities)],
                'status'                     => $status,
                'site_instructions'          => $siteInstructions[$i],
                'parking_status'             => $parkingStatuses[$i],
                'customer_type'              => $client->customer_type,
                'pet_warning'                => null,
                'attached_images'            => [],
                'before_images'              => [],
                'after_images'               => [],
                'done_by_user_id'            => $isCompleted ? $assignedMowerId : null,
                'payment_mode'               => $paymentMode,
                'payment_status'             => $paymentStatus,
                'payment_pending_reason'     => null,
                'special_remarks'            => $client->special_remarks,
                'internal_notes'             => null,
                'created_by'                 => $adminId,
            ]);

            DB::table('job_user_assignments')->insert([
                'job_id'            => $job->id,
                'user_id'           => $assignedMowerId,
                'assignment_date'   => $dates[$i],
                'assignment_status' => $isCompleted ? 'Completed' : 'Assigned',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}
