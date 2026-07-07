<?php

namespace App\Services;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadWeedSpray;
use App\Imports\ClientsImport;
use App\Jobs\GeocodeClientAddressJob;
use App\Models\AccountingLevel;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\ClientRating;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Repositories\ClientRepository;
use App\Support\EquipmentTypes;
use App\Support\SafetyTypes;
use App\Support\ServiceTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientManagementService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly ClientStatisticsService $clientStatisticsService,
        private readonly ActivityLogService $activityLogService,
        private readonly JobManagementService $jobManagementService
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
            'weedSprayOptions' => LeadWeedSpray::values(),
            'recurrenceOptions' => Recurrence::all(),
            'jobTypes' => LeadJobType::values(),
            'safetyOptions' => SafetyTypes::all(),
            'paymentModes' => LeadPaymentMode::values(),
            'paymentStatuses' => ClientPaymentStatus::values(),
            'customerTypes' => ClientCustomerType::values(),
            'parkingStatuses' => JobParkingStatus::values(),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'zones' => Zone::active()->ordered()->get(['id', 'name']),
            'accountingLevels' => AccountingLevel::active()->ordered()->get(['id', 'name', 'description']),
            'clientRatings' => ClientRating::active()->ordered()->get(['id', 'name', 'description']),
            'jobLevels' => JobLevel::active()->ordered()->get(['id', 'name', 'description']),
            'clientTypes' => ['Regular', 'On-Call', 'New'],
            'jobStatuses' => ['Pending', 'Assigned', 'En Route', 'On Site', 'Completed', 'Cancelled'],
        ];
    }

    public function createClient(User $actor, array $data): Client
    {
        $documents = $this->pullDocuments($data);
        $data['created_by'] = $actor->id;
        $client = Client::query()->create($this->prepareClientData($data));
        $this->storeClientDocuments($actor, $client, $documents);
        GeocodeClientAddressJob::dispatch($client->id);
        $this->jobManagementService->createJobFromClient($actor, $client);

        $this->activityLogService->log($actor, 'client.created', 'Customer created.', ['client_id' => $client->id]);

        return $client;
    }

    public function updateClient(User $actor, Client $client, array $data): Client
    {
        $originalScheduleDate = $client->schedule_date?->toDateString();

        $documents = $this->pullDocuments($data);
        $client->fill($this->prepareClientData($data, $client));
        $client->save();
        $this->storeClientDocuments($actor, $client, $documents);

        if ($client->schedule_date !== null && $client->schedule_date->toDateString() !== $originalScheduleDate) {
            $this->applyScheduleChange($actor, $client);
        }

        GeocodeClientAddressJob::dispatch($client->id);
        $this->activityLogService->log($actor, 'client.updated', 'Customer updated.', ['client_id' => $client->id]);

        return $client;
    }

    /**
     * Set a new schedule date on each customer and reschedule (or create) their jobs.
     *
     * @param  Collection<int, Client>  $clients
     */
    public function rescheduleClients(User $actor, Collection $clients, string $scheduledDate, ?string $scheduledTime = null): void
    {
        foreach ($clients as $client) {
            $client->schedule_date = $scheduledDate;
            $client->save();
            $this->applyScheduleChange($actor, $client, $scheduledTime);
            $this->activityLogService->log($actor, 'client.rescheduled', 'Customer schedule updated.', [
                'client_id' => $client->id,
                'scheduled_date' => $scheduledDate,
            ]);
        }
    }

    /**
     * Reschedule the customer's existing jobs to its schedule date, or create a
     * first job from the customer when it has none.
     */
    private function applyScheduleChange(User $actor, Client $client, ?string $scheduledTime = null): void
    {
        if ($client->jobs()->exists()) {
            $this->reschedulePendingJobs($actor, $client, $scheduledTime);
        } else {
            $this->jobManagementService->createJobFromClient($actor, $client);
        }
    }

    private function reschedulePendingJobs(User $actor, Client $client, ?string $scheduledTime = null): void
    {
        $pendingJobs = $client->jobs()
            ->where('status', '!=', JobWorkflowStatus::COMPLETED->value)
            ->whereNull('verified_at')
            ->get();

        if ($pendingJobs->isNotEmpty()) {
            $this->jobManagementService->scheduleJobs($actor, $pendingJobs, $client->schedule_date->toDateString(), $scheduledTime);
        }
    }

    public function deleteClient(User $actor, Client $client): void
    {
        DB::transaction(function () use ($actor, $client): void {
            $activeJobs = $client->jobs()->get();
            if ($activeJobs->isNotEmpty()) {
                $this->jobManagementService->deleteJobs($actor, $activeJobs);
            }

            $client->delete();
            $this->activityLogService->log($actor, 'client.deleted', 'Customer deleted.', ['client_id' => $client->id]);
        });
    }

    public function restoreClient(User $actor, Client $client): void
    {
        DB::transaction(function () use ($actor, $client): void {
            $client->restore();

            $trashedJobs = $client->jobs()->onlyTrashed()->get();
            if ($trashedJobs->isNotEmpty()) {
                $this->jobManagementService->restoreJobs($actor, $trashedJobs);
            }

            $this->activityLogService->log($actor, 'client.restored', 'Customer restored.', ['client_id' => $client->id]);
        });
    }

    /**
     * @return array{imported: int, failed: int, failures: list<array{row: int, identifier: string, reason: list<string>}>, duplicated: int, duplicates: list<array{row: int, identifier: string, reason: list<string>}>}
     */
    public function importClients(User $actor, UploadedFile $file, bool $createJobs = false): array
    {
        $result = (new ClientsImport($actor, $this, $createJobs))->import($file);

        $this->activityLogService->log($actor, 'client.imported', 'Customer file import completed.', [
            'imported_rows' => $result['imported'],
            'failed_rows' => $result['failed'],
            'duplicated_rows' => $result['duplicated'],
            'file' => $file->getClientOriginalName(),
        ]);

        return $result;
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

        return $data;
    }

    /**
     * Pull the uploaded document files out of the request payload.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, UploadedFile>
     */
    private function pullDocuments(array &$data): array
    {
        $documents = $data['documents'] ?? [];
        unset($data['documents']);

        return array_values(array_filter(
            is_array($documents) ? $documents : [$documents],
            static fn ($file): bool => $file instanceof UploadedFile
        ));
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeClientDocuments(User $actor, Client $client, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store("client-documents/{$client->id}", ClientDocument::DISK);

            $client->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'file_type' => $this->resolveDocumentType((string) $file->getMimeType()),
                'uploaded_by' => $actor->id,
            ]);
        }
    }

    private function resolveDocumentType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if ($mime === 'application/pdf') {
            return 'pdf';
        }

        return 'document';
    }
}
