<?php

namespace App\Imports;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Exports\LeadsExport;
use App\Models\EquipmentType;
use App\Models\Lead;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Services\LeadConversionService;
use App\Services\LeadManagementService;
use App\Support\ServiceTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

class LeadsImport
{
    public function __construct(
        private readonly User $actor,
        private readonly LeadManagementService $service,
        private readonly LeadConversionService $conversionService,
    ) {}

    /** Headers that cannot be auto-derived from the column name, or must be skipped (null). */
    private const FIELD_OVERRIDES = [
        '#'            => null,
        'converted at' => null,
        'created at'   => null,
        'mobile'       => 'mobile_number',
        'charges ($)'  => 'charges',
    ];

    /** @var array<string, string|null>|null */
    private static ?array $headerMapCache = null;

    /** @var array<int, string>|null */
    private static ?array $importColumnsCache = null;

    /** @return array<int, string> */
    public static function importColumns(): array
    {
        if (self::$importColumnsCache !== null) {
            return self::$importColumnsCache;
        }

        $skip = array_keys(array_filter(self::FIELD_OVERRIDES, fn ($v) => $v === null));

        return self::$importColumnsCache = array_values(array_filter(
            array_column(LeadsExport::COLUMNS, 'header'),
            fn (string $h) => ! in_array(strtolower($h), $skip, true)
        ));
    }

    /**
     * @return array{imported: int, failed: int, failures: list<array{row: int, identifier: string, reason: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        if (empty($rows)) {
            abort(422, 'The file is empty.');
        }

        // Row 1=title, Row 2=metadata, Row 3=spacer, Row 4=headers (index 3), Row 5+=data (index 4+)
        $rawHeaders = array_map('strval', $rows[3]);
        $this->validateFormat($rawHeaders);

        $normalizedHeaders = $this->normalizeHeaders($rawHeaders);
        $imported = 0;
        $failures = [];

        foreach (array_slice($rows, 4) as $rowIndex => $row) {
            $rowNumber = $rowIndex + 6; // 1-based row number in the spreadsheet (4 header rows + 1 offset)

            $mapped = [];
            foreach ($normalizedHeaders as $idx => $field) {
                if ($field !== null) {
                    $mapped[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
                }
            }

            if (empty($mapped['address'] ?? '')) {
                continue;
            }

            $email = trim($mapped['email'] ?? '');
            $identifier = $email ?: ($mapped['address'] ?? "Row {$rowNumber}");

            if ($email !== '' && Lead::query()->where('email', $email)->exists()) {
                continue;
            }

            try {
                DB::transaction(function () use ($mapped, &$imported): void {
                    $data = $this->buildLeadData($mapped);
                    if (LeadStatus::convertsToClientValue(data_get($data, 'status')) && empty(data_get($data, 'lead_date'))) {
                        throw new \InvalidArgumentException('Lead date is required when converting a lead to client — please provide a lead date.');
                    }
                    $lead = $this->service->createLead($this->actor, $data);

                    if (LeadStatus::convertsToClientValue($lead->status)) {
                        $this->conversionService->convertLeadToClient($lead, $this->actor);
                    }

                    $imported++;
                });
            } catch (\Throwable $e) {
                report($e);
                $failures[] = [
                    'row'        => $rowNumber,
                    'identifier' => $identifier,
                    'reason'     => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => $imported,
            'failed'   => count($failures),
            'failures' => $failures,
        ];
    }

    /** @return array<string, string|null> */
    private static function headerMap(): array
    {
        if (self::$headerMapCache !== null) {
            return self::$headerMapCache;
        }

        $map = [];
        foreach (LeadsExport::COLUMNS as $def) {
            $key       = strtolower($def['header']);
            $map[$key] = array_key_exists($key, self::FIELD_OVERRIDES)
                ? self::FIELD_OVERRIDES[$key]
                : strtolower(str_replace(' ', '_', $def['header']));
        }

        return self::$headerMapCache = $map;
    }

    /**
     * @param  array<int, string>  $rawHeaders
     * @return array<int, string|null>
     */
    private function normalizeHeaders(array $rawHeaders): array
    {
        $map = self::headerMap();
        $normalized = [];
        foreach ($rawHeaders as $idx => $header) {
            $key = strtolower(trim($header));
            $normalized[$idx] = array_key_exists($key, $map) ? $map[$key] : $key;
        }

        return $normalized;
    }

    /** @param array<int, string> $rawHeaders */
    private function validateFormat(array $rawHeaders): void
    {
        $map                = self::headerMap();
        $normalizedUploaded = array_map(fn ($h) => strtolower(trim($h)), $rawHeaders);

        $missing = array_values(array_filter(
            self::importColumns(),
            fn ($h) => ! in_array(strtolower($h), $normalizedUploaded, true)
        ));

        $unrecognized = array_values(array_filter(
            $rawHeaders,
            fn ($h) => trim($h) !== '' && ! array_key_exists(strtolower(trim($h)), $map)
        ));

        $issues = [];
        if (! empty($missing)) {
            $issues[] = 'missing columns: ' . implode(', ', $missing);
        }
        if (! empty($unrecognized)) {
            $issues[] = 'unrecognized columns: ' . implode(', ', $unrecognized);
        }

        if (! empty($issues)) {
            abort(422, 'Invalid file format — ' . implode('; ', $issues) . '. Download the sample file to see the expected format.');
        }
    }

    /** @return array<int, array<int, mixed>> */
    private function readRows(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load((string) $file->getRealPath());

            return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            report($e);
            abort(422, 'Unable to read spreadsheet');
        }
    }

    /**
     * @param  array<string, string>  $mapped
     * @return array<string, mixed>
     */
    private function buildLeadData(array $mapped): array
    {
        $zoneId = null;
        if (! empty($mapped['zone'])) {
            $zoneId = $this->resolveOrCreateByName(Zone::class, $mapped['zone']);
        }

        $recurrenceId = null;
        if (! empty($mapped['recurrence'])) {
            $recurrenceId = $this->resolveOrCreateByName(Recurrence::class, $mapped['recurrence']);
        }

        $equipmentTypeId = null;
        if (! empty($mapped['equipment_type'])) {
            $equipmentTypeId = EquipmentType::query()
                ->where('name', 'like', '%' . trim($mapped['equipment_type']) . '%')
                ->value('id');
        }

        $serviceTypes = ! empty($mapped['service_types'])
            ? ServiceTypes::parseCsvCell($mapped['service_types'])
            : [];

        return [
            'client_name'            => $mapped['client_name'] ?? null,
            'email'                  => $mapped['email'] ?? null,
            'mobile_number'          => $mapped['mobile_number'] ?? null,
            'address'                => $mapped['address'],
            'zone_id'                => $zoneId,
            'service_types'          => $serviceTypes,
            'weed_spray'             => in_array($mapped['weed_spray'] ?? '', LeadWeedSpray::values(), true)
                ? $mapped['weed_spray']
                : null,
            'equipment_type_id'      => $equipmentTypeId,
            'recurrence_id'          => $recurrenceId,
            'job_type'               => in_array($mapped['job_type'] ?? '', LeadJobType::values(), true)
                ? $mapped['job_type']
                : null,
            'charges'                => $mapped['charges'] ?? null,
            'payment_mode'           => in_array($mapped['payment_mode'] ?? '', LeadPaymentMode::values(), true)
                ? $mapped['payment_mode']
                : null,
            'payment_status'         => in_array($mapped['payment_status'] ?? '', LeadPaymentStatus::values(), true)
                ? $mapped['payment_status']
                : LeadPaymentStatus::PENDING->value,
            'remarks'                => $mapped['remarks'] ?? null,
            'property_details'       => $mapped['property_details'] ?? null,
            'latitude'               => $this->parseCoordinate($mapped['latitude'] ?? '', -90, 90),
            'longitude'              => $this->parseCoordinate($mapped['longitude'] ?? '', -180, 180),
            'lead_date'              => $this->parseDate($mapped['lead_date'] ?? ''),
            'lead_time'              => $mapped['lead_time'] ?? null,
            'status'                 => in_array($mapped['status'] ?? '', LeadStatus::values(), true)
                ? $mapped['status']
                : LeadStatus::NEW->value,
            'assigned_sales_user_id' => null,
        ];
    }

    /** @param class-string<Model> $modelClass */
    private function resolveOrCreateByName(string $modelClass, string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $record = $modelClass::query()
            ->where('name', 'like', '%' . $name . '%')
            ->first();

        if ($record) {
            return $record->id;
        }

        return $modelClass::create([
            'name'       => $name,
            'is_active'  => true,
            'sort_order' => 0,
        ])->id;
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                $dt = SpreadsheetDate::excelToDateTimeObject((float) $value);

                return $dt->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseCoordinate(string $value, float $min, float $max): ?float
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($float === false || $float < $min || $float > $max) {
            return null;
        }

        return $float;
    }

}
