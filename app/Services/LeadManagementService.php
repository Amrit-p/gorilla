<?php

namespace App\Services;

use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Events\LeadConvertedToClient;
use App\Jobs\GeocodeLeadAddressJob;
use App\Models\EquipmentType;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Repositories\LeadRepository;
use App\Support\CrmRoles;
use App\Support\EquipmentTypes;
use App\Support\GoogleMapsSettings;
use App\Support\ServiceTypes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadManagementService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly ActivityLogService $activityLogService,
        private readonly LeadConversionService $leadConversionService
    ) {}

    public function paginatedLeads(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->leadRepository->paginatedList($filters, $perPage);
    }

    public function exportLeads(array $filters): Collection
    {
        return $this->leadRepository->exportList($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return [
            'statuses' => LeadStatus::values(),
            'salesUsers' => User::query()
                ->role(CrmRoles::SALES_MANAGER)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'serviceTypes' => ServiceTypes::all(),
            'weedSprayOptions' => \App\Enums\LeadWeedSpray::values(),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'recurrences' => Recurrence::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'jobTypes' => \App\Enums\LeadJobType::values(),
            'paymentModes' => \App\Enums\LeadPaymentMode::values(),
            'paymentStatuses' => LeadPaymentStatus::values(),
            'googleMapsKey' => GoogleMapsSettings::apiKey(),
            'zones' => Zone::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function createLead(User $actor, array $data): Lead
    {
        return DB::transaction(function () use ($actor, $data): Lead {
            $lead = Lead::query()->create($this->prepareLeadData($data));
            $this->maybeGeocode($lead);
            $lead = $this->applyConversionIfNeeded($actor, $lead, $data['status'] ?? $lead->status);

            $this->activityLogService->log($actor, 'lead.created', 'Lead created.', ['lead_id' => $lead->id]);

            return $lead->fresh(['equipmentType:id,name,color_code', 'client:id,lead_id']);
        });
    }

    public function updateLead(User $actor, Lead $lead, array $data): Lead
    {
        if ($lead->is_locked) {
            abort(422, 'This lead is locked and cannot be edited.');
        }

        return DB::transaction(function () use ($actor, $lead, $data): Lead {
            $lead->fill($this->prepareLeadData($data, $lead));
            $lead->save();
            $this->maybeGeocode($lead);
            $lead = $this->applyConversionIfNeeded($actor, $lead, $lead->status);

            $this->activityLogService->log($actor, 'lead.updated', 'Lead updated.', ['lead_id' => $lead->id]);

            return $lead->fresh(['equipmentType:id,name,color_code', 'client:id,lead_id']);
        });
    }

    public function deleteLead(User $actor, Lead $lead): void
    {
        if ($lead->is_locked) {
            abort(422, 'This lead is locked and cannot be deleted.');
        }

        $lead->delete();
        $this->activityLogService->log($actor, 'lead.deleted', 'Lead deleted.', ['lead_id' => $lead->id]);
    }

    public function updateStatus(User $actor, Lead $lead, string $status): Lead
    {
        return DB::transaction(function () use ($actor, $lead, $status): Lead {
            $lead = Lead::query()->whereKey($lead->id)->lockForUpdate()->firstOrFail();
            $lead->status = $status;
            $lead->save();

            $lead = $this->applyConversionIfNeeded($actor, $lead, $status);

            $this->activityLogService->log($actor, 'lead.status_updated', 'Lead status updated.', [
                'lead_id' => $lead->id,
                'status' => $status,
            ]);

            return $lead->fresh(['equipmentType:id,name,color_code', 'client:id,lead_id']);
        });
    }

    public function addLeadNote(User $actor, Lead $lead, string $note): LeadNote
    {
        $leadNote = $lead->leadNotes()->create([
            'user_id' => $actor->id,
            'note' => $note,
        ]);

        $this->activityLogService->log($actor, 'lead.note_added', 'Lead note added.', [
            'lead_id' => $lead->id,
            'lead_note_id' => $leadNote->id,
        ]);

        return $leadNote;
    }

    /**
     * @return array<int, string>
     */
    public static function importCsvColumns(): array
    {
        return [
            'client_name',
            'email',
            'mobile_number',
            'address',
            'service_types',
            'weed_spray',
            'equipment_type_id',
            'recurrence_id',
            'job_type',
            'charges',
            'payment_mode',
            'payment_status',
            'remarks',
            'latitude',
            'longitude',
            'lead_date',
            'lead_time',
            'status',
            'assigned_sales_user_id',
        ];
    }

    public function downloadImportSampleCsv(): StreamedResponse
    {
        $headers = self::importCsvColumns();
        $equipmentId = EquipmentType::query()->where('is_active', true)->value('id');
        $sampleRow = [
            'Sample Property',
            'lead@example.com',
            '555-0100',
            '123 Green Street',
            implode(', ', array_slice(ServiceTypes::all(), 0, 2)) ?: 'Mulching',
            \App\Enums\LeadWeedSpray::NO->value,
            (string) ($equipmentId ?? ''),
            (string) (Recurrence::query()->where('is_active', true)->value('id') ?? ''),
            \App\Enums\LeadJobType::REGULAR->value,
            '75.00',
            \App\Enums\LeadPaymentMode::CASH->value,
            LeadPaymentStatus::PENDING->value,
            'Gate code 1234',
            '',
            '',
            now()->toDateString(),
            '09:00',
            LeadStatus::NEW->value,
            '',
        ];

        return response()->streamDownload(function () use ($headers, $sampleRow): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fputcsv($handle, $headers);
            fputcsv($handle, $sampleRow);
            fclose($handle);
        }, 'leads-import-sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function importFromCsv(User $actor, UploadedFile $file): int
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            abort(422, 'Unable to read CSV file.');
        }

        $header = fgetcsv($handle) ?: [];
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $mapped = array_combine($header, $row);
            if (! is_array($mapped) || empty($mapped['address'])) {
                continue;
            }

            $lead = $this->createLead($actor, [
                'client_name' => $mapped['client_name'] ?? null,
                'email' => $mapped['email'] ?? null,
                'mobile_number' => $mapped['mobile_number'] ?? null,
                'address' => $mapped['address'] ?? null,
                'service_types' => $this->resolveImportServiceTypes($mapped),
                'weed_spray' => $mapped['weed_spray'] ?? null,
                'equipment_type_id' => is_numeric($mapped['equipment_type_id'] ?? null) ? (int) $mapped['equipment_type_id'] : null,
                'recurrence_id' => is_numeric($mapped['recurrence_id'] ?? null) ? (int) $mapped['recurrence_id'] : null,
                'job_type' => $mapped['job_type'] ?? null,
                'charges' => $mapped['charges'] ?? null,
                'payment_mode' => $mapped['payment_mode'] ?? null,
                'payment_status' => $mapped['payment_status'] ?? LeadPaymentStatus::PENDING->value,
                'remarks' => $mapped['remarks'] ?? null,
                'latitude' => $mapped['latitude'] ?? null,
                'longitude' => $mapped['longitude'] ?? null,
                'lead_date' => $mapped['lead_date'] ?? null,
                'lead_time' => $mapped['lead_time'] ?? null,
                'status' => in_array(($mapped['status'] ?? ''), LeadStatus::values(), true)
                    ? $mapped['status']
                    : LeadStatus::NEW->value,
                'assigned_sales_user_id' => is_numeric($mapped['assigned_sales_user_id'] ?? null)
                    ? (int) $mapped['assigned_sales_user_id']
                    : null,
            ]);

            $imported++;
        }

        fclose($handle);

        $this->activityLogService->log($actor, 'lead.csv_imported', 'Lead CSV import completed.', [
            'imported_rows' => $imported,
            'file' => $file->getClientOriginalName(),
        ]);

        return $imported;
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array<int, string>
     */
    private function resolveImportServiceTypes(array $mapped): array
    {
        if (! empty($mapped['service_types'])) {
            return ServiceTypes::parseCsvCell((string) $mapped['service_types']);
        }

        if (! empty($mapped['service_type'])) {
            return ServiceTypes::parseCsvCell((string) $mapped['service_type']);
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareLeadData(array $data, ?Lead $existingLead = null): array
    {
        $data['status'] ??= LeadStatus::NEW->value;
        $data['payment_status'] ??= LeadPaymentStatus::PENDING->value;

        if (isset($data['service_types']) && is_array($data['service_types'])) {
            $data['service_types'] = array_values(array_filter(
                $data['service_types'],
                static fn ($type): bool => in_array($type, ServiceTypes::all(), true)
            ));
        }

        if (empty($data['client_name'])) {
            $address = trim((string) ($data['address'] ?? $existingLead?->address ?? ''));
            $data['client_name'] = $address !== ''
                ? Str::limit($address, 120)
                : ($existingLead?->client_name ?? 'Lead');
        }

        if (array_key_exists('lead_time', $data) && $data['lead_time'] === '') {
            $data['lead_time'] = null;
        }

        return $data;
    }

    private function maybeGeocode(Lead $lead): void
    {
        if ($lead->latitude !== null && $lead->longitude !== null) {
            return;
        }

        GeocodeLeadAddressJob::dispatch($lead->id);
    }

    private function applyConversionIfNeeded(User $actor, Lead $lead, string $status): Lead
    {
        if (! LeadConversionService::shouldConvert($status)) {
            return $lead;
        }

        $lead->is_locked = true;
        $lead->converted_at ??= now();
        $lead->queued_for_scheduling = true;
        $lead->save();

        $client = $this->leadConversionService->convertLeadToClient($lead, $actor);
        $this->leadConversionService->logConversion($actor, $lead, $client);

        event(new LeadConvertedToClient($lead->fresh(), $actor));

        return $lead->fresh();
    }
}
