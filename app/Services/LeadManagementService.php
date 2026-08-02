<?php

namespace App\Services;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Events\LeadConvertedToClient;
use App\Imports\LeadsImport;
use App\Jobs\GeocodeLeadAddressJob;
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

    public function paginatedConvertedLeads(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->leadRepository->paginatedList($filters, $perPage, convertedOnly: true);
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
            'statuses' => LeadStatus::selectableValues(),
            'salesUsers' => User::query()
                ->role(CrmRoles::SALES_MANAGER)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'serviceTypes' => ServiceTypes::all(),
            'weedSprayOptions' => LeadWeedSpray::values(),
            'equipmentTypes' => EquipmentTypes::selectOptions(),
            'recurrences' => Recurrence::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'jobTypes' => LeadJobType::values(),
            'paymentModes' => LeadPaymentMode::values(),
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

            return $lead->fresh(['equipmentType:id,name,color_code']);
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

            return $lead->fresh(['equipmentType:id,name,color_code']);
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

            return $lead->fresh(['equipmentType:id,name,color_code']);
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
     * @return array{imported: int, failed: int, failures: list<array{row: int, identifier: string, reason: string}>}
     */
    public function importLeads(User $actor, UploadedFile $file): array
    {
        $result = (new LeadsImport($actor, $this, $this->leadConversionService))->import($file);

        $this->activityLogService->log($actor, 'lead.imported', 'Lead file import completed.', [
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

        $job = $this->leadConversionService->convertLeadToJob($lead, $actor);
        $this->leadConversionService->logConversion($actor, $lead, $job);

        event(new LeadConvertedToClient($lead->fresh(), $actor));

        return $lead->fresh();
    }
}
