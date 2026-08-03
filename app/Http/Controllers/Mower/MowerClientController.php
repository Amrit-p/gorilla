<?php

namespace App\Http\Controllers\Mower;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadWeedSpray;
use App\Helpers\OptimizationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mower\StoreMowerQuickJobRequest;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\MowerClientJobCreatedNotification;
use App\Services\JobManagementService;
use App\Support\CrmRoles;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MowerClientController extends Controller
{
    public function __construct(
        private readonly JobManagementService $jobManagementService
    ) {}

    public function create(): View
    {
        return view('mower.jobs.create', [
            'serviceTypes' => ServiceTypes::all(),
            'weedSprayOptions' => LeadWeedSpray::values(),
            'recurrenceOptions' => Recurrence::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'jobTypes' => LeadJobType::values(),
            'paymentModes' => JobOperationalPaymentMode::values(),
            'customerTypes' => JobCustomerType::values(),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'zones' => Zone::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreMowerQuickJobRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        $address = trim((string) ($data['address'] ?? ''));
        $scheduledDate = $data['scheduled_date'] ?? $data['schedule_date'] ?? now()->toDateString();

        $job = $this->jobManagementService->createJob($actor, [
            'customer_name' => $data['customer_name'] ?? ($address !== '' ? $address : 'Customer'),
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'client_address' => $address,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'required_services' => $data['service_types'],
            'weed_spray' => $data['weed_spray'],
            'job_type' => $data['job_type'],
            'recurrence_id' => $data['recurrence_id'],
            'is_recurring' => true,
            'equipment_type_id' => $data['equipment_type_id'],
            'customer_type' => $data['customer_type'],
            'payment_mode' => $data['payment_mode'],
            'payment_status' => JobOperationalPaymentStatus::PENDING->value,
            'charges' => $data['charges'] ?? null,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => '08:00',
            'estimated_duration_minutes' => 60,
            'zone_id' => $data['zone_id'] ?? null,
            'site_instructions' => $data['site_instructions'] ?? $data['additional_site_instructions'] ?? null,
            'special_remarks' => $data['special_remarks'] ?? null,
            'done_by_user_id' => $actor->id,
        ], $request->file('images', []) ?: []);

        if ($job) {
            User::query()
                ->role([CrmRoles::OFFICE_MANAGER, CrmRoles::SALES_MANAGER])
                ->get()
                ->each(function (User $manager) use ($job, $actor): void {
                    $manager->notify(new MowerClientJobCreatedNotification($job, $actor));
                    OptimizationHelper::forgetNotificationUnreadCount($manager->id);
                });
        }

        return redirect()
            ->route('mower.index')
            ->with('success', 'Job created successfully.');
    }
}
