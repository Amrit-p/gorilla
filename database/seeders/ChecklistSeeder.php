<?php

namespace Database\Seeders;

use App\Models\Checklist;
use App\Models\ChecklistPoint;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $checklists = [
            [
                'name'   => 'Safety Checklist',
                'points' => [
                    [
                        'heading' => 'Lawn Mowing Safety Checklist',
                        'text'    => "Inspect the entire area before mowing. Remove rocks, bricks, sticks, metal objects, nails, wires, toys, and other debris from the ground.\n" .
                                     "Ensure there are no hidden obstacles that could damage the mower blade or become projectiles.\n" .
                                     "Avoid mowing on excessively steep slopes or unstable terrain.\n" .
                                     "Ensure there are no exposed electrical wires or electrical hazards in the work area.\n" .
                                     "Verify that the ground is dry enough for safe operation and not excessively muddy or slippery.\n" .
                                     "Ensure children, pets, and unauthorized persons stay clear of the work area.\n" .
                                     "Be aware of nearby vehicles, windows, fences, and property that could be damaged by flying debris.",
                    ],
                    [
                        'heading' => 'Vehicle Safety',
                        'text'    => "Park the vehicle in a safe and legal location close to the job site.\n" .
                                     "Do not block roads, driveways, footpaths, or emergency access routes.\n" .
                                     "Lock the vehicle when unattended.\n" .
                                     "Keep valuable equipment secured and under supervision.\n" .
                                     "Ensure all tools and equipment are properly stored before transport.",
                    ],
                    [
                        'heading' => 'Equipment Inspection',
                        'text'    => "Check blades for wear, cracks, or damage.\n" .
                                     "Ensure all guards, shields, and safety devices are installed and functional.\n" .
                                     "Check engine oil, fuel, hydraulic fluid, and coolant levels where applicable.\n" .
                                     "Inspect belts, cables, handles, and controls.",
                    ],
                    [
                        'heading' => 'Personal Protective Equipment (PPE)',
                        'text'    => "Wear safety glasses or a face shield.\n" .
                                     "Wear hearing protection.\n" .
                                     "Wear safety boots with non-slip soles.\n" .
                                     "Wear gloves when handling equipment or debris.\n" .
                                     "Use high-visibility clothing when working near roads or public areas.",
                    ],
                    [
                        'heading' => 'Safe Operating Procedures',
                        'text'    => "Never place hands or feet near moving blades.\n" .
                                     "Stop the engine before clearing blockages or performing maintenance.\n" .
                                     "Use extra caution when working on slopes.",
                    ],
                    [
                        'heading' => 'End of Job Checklist',
                        'text'    => "Secure all equipment properly in the vehicle.\n" .
                                     "Verify that no tools or materials are left behind.\n" .
                                     "Report any incidents, damage, or maintenance requirements.",
                    ],
                ],
            ],
        ];

        foreach ($checklists as $checklistData) {
            $checklist = Checklist::firstOrCreate(
                ['name' => $checklistData['name']],
            );

            if ($checklist->points()->doesntExist()) {
                foreach ($checklistData['points'] as $index => $pointData) {
                    ChecklistPoint::create([
                        'checklist_id' => $checklist->id,
                        'heading'      => $pointData['heading'],
                        'text'         => $pointData['text'],
                        'sort_order'   => $index,
                    ]);
                }
            }
        }
    }
}
