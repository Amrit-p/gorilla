<?php

namespace App\Services;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Jobs\GeocodeClientAddressJob;
use App\Models\Client;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\AccountingLevel;
use App\Models\Zone;
use App\Repositories\ClientRepository;
use App\Support\EquipmentTypes;
use App\Support\SafetyTypes;
use App\Support\ServiceTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ClientManagementService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly ClientStatisticsService $clientStatisticsService,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function paginatedClients(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->clientRepository->paginatedList($filters, $perPage);
    }

    public function exportClients(array $filters): Collection
    {
        return $this->clientRepository->exportList($filters);
    }

    public function findForShow(int $clientId): ?Client
    {
        return $this->clientRepository->findForShow($clientId);
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function customerStatistics(Client $client): array
    {
        return $this->clientStatisticsService->forClient($client);
    }

    /**
     * @deprecated This method is no longer used and will be removed in a future release. use JobManagementService::paginatedJobs instead.
     * @return LengthAwarePaginator
     */
    public function paginatedCustomerJobs(Client $client, array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->clientRepository->paginatedJobsForClient($client->id, $filters, $perPage);
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function jobTabStatistics(Client $client, ?string $statusFilter): array
    {
        return $this->clientStatisticsService->forClientJobsTab($client, $statusFilter);
    }

    public function formOptions(): array
    {
        return [
            'serviceTypes' => ServiceTypes::all(),
            'weedSprayOptions' => \App\Enums\LeadWeedSpray::values(),
            'recurrenceOptions' => Recurrence::all(),
            'jobTypes' => \App\Enums\LeadJobType::values(),
            'safetyOptions' => SafetyTypes::all(),
            'paymentModes' => \App\Enums\LeadPaymentMode::values(),
            'paymentStatuses' => ClientPaymentStatus::values(),
            'customerTypes' => ClientCustomerType::values(),
            'parkingStatuses' => JobParkingStatus::values(),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'zones' => Zone::active()->ordered()->get(['id', 'name']),
            'accountingLevels' => AccountingLevel::active()->ordered()->get(['id', 'name', 'description']),
            'clientTypes' => ['Regular', 'On-Call', 'New'],
            'jobStatuses' => ['Pending', 'Assigned', 'En Route', 'On Site', 'Completed', 'Cancelled'],
        ];
    }

    public function createClient(User $actor, array $data): Client
    {
        $data['created_by'] = $actor->id;
        $client = Client::query()->create($this->prepareClientData($data));
        GeocodeClientAddressJob::dispatch($client->id);

        $this->activityLogService->log($actor, 'client.created', 'Customer created.', ['client_id' => $client->id]);

        return $client;
    }

    public function updateClient(User $actor, Client $client, array $data): Client
    {
        $client->fill($this->prepareClientData($data, $client));
        $client->save();

        GeocodeClientAddressJob::dispatch($client->id);
        $this->activityLogService->log($actor, 'client.updated', 'Customer updated.', ['client_id' => $client->id]);

        return $client;
    }

    public function deleteClient(User $actor, Client $client): void
    {
        $client->delete();
        $this->activityLogService->log($actor, 'client.deleted', 'Customer deleted.', ['client_id' => $client->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareClientData(array $data, ?Client $existingClient = null): array
    {
        if (empty($data['name'])) {
            $address = trim((string) ($data['address'] ?? $existingClient?->address ?? ''));
            $data['name'] = $address !== '' ? Str::limit($address, 120) : ($existingClient?->name ?? 'Customer');
        }

        $jobType = (string) ($data['job_type'] ?? $existingClient?->job_type ?? 'Regular');
        $data['client_type'] = match ($jobType) {
            'On call' => 'On-Call',
            'New' => 'New',
            default => 'Regular',
        };

        $data['customer_type'] ??= ClientCustomerType::DONT_KNOW->value;

        $safetyConcerns = $data['safety_concerns'] ?? [];
        if (! in_array('Any Other', $safetyConcerns, true)) {
            $data['safety_other'] = null;
        }

        if (($data['payment_status'] ?? null) !== ClientPaymentStatus::PENDING->value) {
            $data['payment_status_reason'] = null;
        }

        return $data;
    }
}
