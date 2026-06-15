<?php

namespace App\Http\Controllers\Mower;

use App\Helpers\OptimizationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mower\StoreMowerClientRequest;
use App\Models\User;
use App\Notifications\MowerClientJobCreatedNotification;
use App\Services\ClientManagementService;
use App\Services\JobManagementService;
use App\Support\CrmRoles;
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
        $client = $this->clientManagementService->createClient($actor, $request->validated());

        $jobData = [
            'client_id' => $client->id,
            'client_address' => $client->address,
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
            'recurrence_id' => $client->recurrence_id,
            'zone_id' => $client->zone_id,
            'equipment_type_id' => $client->equipment_type_id,
            'job_level_id' => $client->job_level_id,
            'required_services' => $client->service_types ?? [],
            'payment_mode' => $client->payment_mode,
            'payment_status' => 'Pending',
            'charges' => $client->charges,
            'scheduled_date' => $client->schedule_date?->toDateString() ?? now()->toDateString(),
            'scheduled_time' => '08:00',
            'estimated_duration_minutes' => 60,
            'customer_type' => $client->customer_type,
            'site_instructions' => $client->additional_site_instructions,
            'special_remarks' => $client->special_remarks,
            'done_by_user_id' => $actor->id,
            'employee_ids' => [$actor->id],
        ];

        $job = $this->jobManagementService->createJob($actor, $jobData);

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
            ->with('success', 'Customer and job created successfully.');
    }
}
