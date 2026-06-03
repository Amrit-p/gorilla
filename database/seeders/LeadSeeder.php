<?php

namespace Database\Seeders;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Models\Lead;
use App\Models\ServiceType;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    private const COUNT = 20;

    // Realistic QLD bounding box for coordinates
    private const LAT_MIN = -28.5;
    private const LAT_MAX = -27.0;
    private const LNG_MIN = 152.5;
    private const LNG_MAX = 153.5;

    private const QLD_SUBURBS = [
        'Sunnybank', 'Mount Gravatt', 'Capalaba', 'Aspley', 'Indooroopilly',
        'Redland Bay', 'Ipswich', 'Springfield', 'Logan', 'Carindale',
        'Paddington', 'Wynnum', 'Kenmore', 'Robina', 'Ferny Grove',
        'Wavell Heights', 'Mansfield', 'Chapel Hill', 'Mitchelton', 'Eight Mile Plains',
    ];

    public function run(): void
    {
        $adminId = User::where('email', 'admin@mowingcrm.test')->value('id');
        $faker   = Faker::create('en_AU');

        for ($i = 0; $i < self::COUNT; $i++) {
            $status   = $faker->randomElement(LeadStatus::selectableValues());
            $converts = LeadStatus::tryFrom($status)?->convertsToClient() ?? false;

            // Converted leads need a past lead_date so converted_at can follow it.
            $leadDate = $converts
                ? $faker->dateTimeBetween('2025-01-01', 'now')
                : $faker->dateTimeBetween('2025-01-01', '2026-12-31');

            Lead::create([
                'zone_id'                => $faker->numberBetween(1, 8),
                'client_name'            => $faker->name(),
                'email'                  => $faker->safeEmail(),
                'mobile_number'          => '04' . $faker->numerify('## ### ###'),
                'address'                => $faker->streetAddress() . ', ' . $faker->randomElement(self::QLD_SUBURBS) . ' QLD ' . $faker->numberBetween(4000, 4999),
                'service_types'          => $faker->randomElements(ServiceType::where('is_active', true)->pluck('name')->all(), $faker->numberBetween(1, 2)),
                'weed_spray'             => $faker->randomElement(LeadWeedSpray::values()),
                'equipment_type_id'      => $faker->numberBetween(1, 3),
                'recurrence_id'          => $faker->numberBetween(1, 5),
                'job_type'               => $faker->randomElement(LeadJobType::values()),
                'charges'                => $faker->randomFloat(2, 80, 350),
                'payment_mode'           => $faker->randomElement(LeadPaymentMode::values()),
                'payment_status'         => $faker->randomElement(LeadPaymentStatus::values()),
                'remarks'                => $faker->optional(0.6)->sentence(),
                'property_details'       => $faker->optional(0.4)->sentence(),
                'latitude'               => $faker->randomFloat(6, self::LAT_MIN, self::LAT_MAX),
                'longitude'              => $faker->randomFloat(6, self::LNG_MIN, self::LNG_MAX),
                'lead_date'              => $leadDate->format('Y-m-d'),
                'lead_time'              => sprintf('%02d:%02d', $faker->numberBetween(7, 16), $faker->randomElement([0, 30])),
                'status'                 => $status,
                'is_locked'              => $converts,
                'queued_for_scheduling'  => $converts ? false : $faker->boolean(20),
                'converted_at'           => $converts ? $faker->dateTimeBetween($leadDate, 'now') : null,
                'assigned_sales_user_id' => $adminId,
            ]);
        }
    }
}
