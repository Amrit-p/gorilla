<?php

namespace App\Http\Controllers\Mower;

use App\Enums\JobCustomerType;
use App\Enums\JobParkingStatus;
use App\Helpers\OptimizationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mower\StoreMowerClientRequest;
use App\Models\User;
use App\Notifications\MowerClientJobCreatedNotification;
use App\Services\ClientManagementService;
use App\Services\JobManagementService;
use App\Support\CrmRoles;
use App\Support\EstimatedDurationMinutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MowerClientController extends Controller
{
    public function __construct(
        private readonly ClientManagementService $clientManagementService,
        private readonly JobManagementService $jobManagementService
    ) {}

    public function create(): View
    {
        return view('mower.clients.create', $this->clientManagementService->formOptions());
    }

    public function store(StoreMowerClientRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $client = $this->clientManagementService->createClient($actor, $request->validated(), createInitialJob: false);

        $job = $this->jobManagementService->createJob($actor, [
            'client_id' => $client->id,
            'customer_name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'weed_spray' => $client->weed_spray,
            'job_type' => $client->job_type,
            'property_details' => $client->property_details,
            'notes' => $client->notes,
            'client_address' => $client->address,
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
            'recurrence_id' => $client->recurrence_id,
            'is_recurring' => $client->recurrence_id !== null,
            'zone_id' => $client->zone_id,
            'equipment_type_id' => $client->equipment_type_id,
            'job_level_id' => $client->job_level_id,
            'required_services' => $client->service_types ?? [],
            'payment_mode' => $client->payment_mode,
            'payment_status' => 'Pending',
            'charges' => $client->charges,
            'scheduled_date' => $client->schedule_date?->toDateString() ?? now()->toDateString(),
            'scheduled_time' => '08:00',
            'estimated_duration_minutes' => EstimatedDurationMinutes::resolve($client->estimated_time),
            'customer_type' => $client->customer_type ?: JobCustomerType::DONT_KNOW->value,
            'parking_status' => $client->parking_status ?: JobParkingStatus::values()[0] ?? null,
            'site_instructions' => $client->additional_site_instructions,
            'special_remarks' => $client->special_remarks,
            'done_by_user_id' => $actor->id,
        ]);

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
