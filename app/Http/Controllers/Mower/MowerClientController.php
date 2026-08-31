<?php

namespace App\Http\Controllers\Mower;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadWeedSpray;
use App\Helpers\OptimizationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mower\StoreMowerQuickJobRequest;
use App\Models\Recurrence;
use App\Models\User;
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
            'paymentModes' => JobOperationalPaymentMode::values(),
            'chargesRequiredPaymentModes' => StoreMowerQuickJobRequest::PAYMENT_MODES_REQUIRING_CHARGES,
            'customerTypes' => JobCustomerType::values(),
            'equipmentTypes' => EquipmentTypes::mowerSelectOptions(),
            'workflowStatuses' => JobWorkflowStatus::values(),
            'defaultStatus' => JobWorkflowStatus::PENDING->value,
        ]);
    }

    public function store(StoreMowerQuickJobRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $request->validated();

        $address = trim((string) ($data['address'] ?? ''));
        $scheduledDate = $data['scheduled_date'] ?? $data['schedule_date'] ?? now()->toDateString();

        $job = $this->jobManagementService->createJob($actor, [
            'customer_name' => $address !== '' ? $address : 'Customer',
            'email' => $data['email'] ?? null,
            'client_address' => $address,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'required_services' => $data['service_types'],
            'weed_spray' => $data['weed_spray'],
            'recurrence_id' => $data['recurrence_id'],
            'is_recurring' => true,
            'equipment_type_id' => $data['equipment_type_id'],
            'van_number' => $data['van_number'],
            'customer_type' => $data['customer_type'],
            'payment_mode' => $data['payment_mode'],
            'payment_status' => JobOperationalPaymentStatus::PENDING->value,
            'charges' => $data['charges'] ?? null,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => '08:00',
            'estimated_duration_minutes' => 60,
            'special_remarks' => $data['special_remarks'],
            'status' => $data['status'],
            'done_by_user_id' => $actor->id,
        ], $request->file('images', []) ?: []);

        // createJob() swallows failures and returns null, so a false "success"
        // here would leave the mower believing a job exists that never saved.
        if (! $job) {
            return redirect()
                ->route('mower.jobs.create')
                ->withInput()
                ->with('error', 'Could not create the job. Please try again, or contact the office if it keeps failing.');
        }

        User::query()
            ->role([CrmRoles::OFFICE_MANAGER, CrmRoles::SALES_MANAGER])
            ->get()
            ->each(function (User $manager) use ($job, $actor): void {
                $manager->notify(new MowerClientJobCreatedNotification($job, $actor));
                OptimizationHelper::forgetNotificationUnreadCount($manager->id);
            });

        return redirect()
            ->route('mower.index')
            ->with('success', 'Job created successfully.');
    }
}
