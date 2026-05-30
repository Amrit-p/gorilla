<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadServiceType;
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
     * @return array{imported: int, failed: int, failures: list<array{row: int, identifier: string, reason: list<string>}>, duplicated: int, duplicates: list<array{row: int, identifier: string, reason: list<string>}>}
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        if (empty($rows)) {
            throw new \InvalidArgumentException('The file is empty.');
        }

        // Row 1=title, Row 2=metadata, Row 3=spacer, Row 4=headers (index 3), Row 5+=data (index 4+)
        $rawHeaders = array_map('strval', $rows[3]);
        $this->validateFormat($rawHeaders);

        $normalizedHeaders = $this->normalizeHeaders($rawHeaders);
        $imported      = 0;
        $importedRows  = [];
        $failures      = [];
        $duplicates    = [];

        foreach (array_slice($rows, 4) as $rowIndex => $row) {
            $rowNumber = $rowIndex + 6; // 1-based row number in the spreadsheet (4 header rows + 1 offset)

            $mapped = [];
            foreach ($normalizedHeaders as $idx => $field) {
                if ($field !== null) {
                    $mapped[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
                }
            }

            if (empty(data_get($mapped, 'address', ''))) {
                continue;
            }

            $email      = data_get($mapped, 'email', '');
            $identifier = $email ?: data_get($mapped, 'address', "Row {$rowNumber}");

            if ($email !== '' && Lead::query()->where('email', $email)->exists()) {
                $duplicates[] = [
                    'row'        => $rowNumber,
                    'identifier' => $identifier,
                    'reason'     => ["A lead with email \"{$email}\" already exists."],
                ];
                continue;
            }

            $validationErrors = $this->validateMappedRow($mapped);
            if (! empty($validationErrors)) {
                $failures[] = [
                    'row'        => $rowNumber,
                    'identifier' => $identifier,
                    'reason'     => $validationErrors,
                ];
                continue;
            }

            try {
                DB::transaction(function () use ($mapped, $rowNumber, $identifier, &$imported, &$importedRows): void {
                    $data = $this->buildLeadData($mapped);
                    if (LeadStatus::convertsToClientValue(data_get($data, 'status')) && empty(data_get($data, 'lead_date'))) {
                        throw new \InvalidArgumentException('Lead date is required when converting a lead to client — please provide a lead date.');
                    }
                    $lead = $this->service->createLead($this->actor, $data);

                    if (LeadStatus::convertsToClientValue($lead->status)) {
                        $this->conversionService->convertLeadToClient($lead, $this->actor);
                    }

                    $imported++;
                    $importedRows[] = ['row' => $rowNumber, 'identifier' => $identifier];
                });
            } catch (\Throwable $e) {
                report($e);
                $failures[] = [
                    'row'        => $rowNumber,
                    'identifier' => $identifier,
                    'reason'     => [$e->getMessage()],
                ];
            }
        }

        return [
            'imported'      => $imported,
            'imported_rows' => $importedRows,
            'failed'        => count($failures),
            'failures'      => $failures,
            'duplicated'    => count($duplicates),
            'duplicates'    => $duplicates,
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

        if (! empty($issues)) {
            throw new \InvalidArgumentException('Invalid file format — ' . implode('; ', $issues) . '. Download the sample file to see the expected format.');
        }
    }

    /** @return array<int, array<int, mixed>> */
    private function readRows(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load((string) $file->getRealPath());
        } catch (\Throwable $e) {
            report($e);
            throw new \InvalidArgumentException('Unable to read spreadsheet.');
        }


        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }


    /**
     * Mirrors the rules in LeadsExport::applyValidations() as PHP-side checks.
     * Keeps the same constraints (lengths, ranges, enum values) in one place.
     *
     * @param  array<string, string>  $mapped
     * @return list<string>
     */
    private function validateMappedRow(array $mapped): array
    {
        $errors = [];

        // B: Client Name — required, 1–255 chars (matches addTextValidation min:1 max:255)
        $clientName = data_get($mapped, 'client_name', '');
        if ($clientName === '' || strlen($clientName) > 255) {
            $errors[] = 'Client name is required and must not exceed 255 characters.';
        }

        // C: Email — optional, basic format check (matches addEmailValidation)
        $email = data_get($mapped, 'email', '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address (e.g. user@example.com).';
        }

        // D: Mobile — optional, max 20 chars (matches addTextValidation min:0 max:20)
        if (strlen(data_get($mapped, 'mobile_number', '')) > 20) {
            $errors[] = 'Mobile number must not exceed 20 characters.';
        }

        // G: Service Types — warning-style in Excel; validate each comma-separated value
        $rawServiceTypes = data_get($mapped, 'service_types', '');
        if ($rawServiceTypes !== '') {
            $given      = array_map('trim', explode(',', $rawServiceTypes));
            $valid      = LeadServiceType::values();
            $validLower = array_map('strtolower', $valid);
            $invalid    = array_filter($given, fn ($g) => ! in_array(strtolower($g), $validLower, true));
            if (! empty($invalid)) {
                $errors[] = 'Invalid service type(s): ' . implode(', ', $invalid) . '. Valid options: ' . implode(', ', $valid) . '.';
            }
        }

        // H: Equipment Type — must match a known name if provided
        $equipmentName = data_get($mapped, 'equipment_type', '');
        if ($equipmentName !== '') {
            $exists = EquipmentType::query()->where('name', 'like', '%' . trim($equipmentName) . '%')->exists();
            if (! $exists) {
                $errors[] = "Equipment type \"{$equipmentName}\" was not found.";
            }
        }

        // I: Job Type — must be a valid enum value if provided
        $jobType = data_get($mapped, 'job_type', '');
        if ($jobType !== '' && ! in_array($jobType, LeadJobType::values(), true)) {
            $errors[] = 'Invalid job type "' . $jobType . '". Valid options: ' . implode(', ', LeadJobType::values()) . '.';
        }

        // J: Charges — decimal >= 0 if provided (matches addNumericValidation >= 0)
        $charges = data_get($mapped, 'charges', '');
        if ($charges !== '') {
            $chargesFloat = filter_var($charges, FILTER_VALIDATE_FLOAT);
            if ($chargesFloat === false || $chargesFloat < 0) {
                $errors[] = 'Charges must be a number greater than or equal to 0.';
            }
        }

        // K: Payment Mode — must be a valid enum value if provided
        $paymentMode = data_get($mapped, 'payment_mode', '');
        if ($paymentMode !== '' && ! in_array($paymentMode, LeadPaymentMode::values(), true)) {
            $errors[] = 'Invalid payment mode "' . $paymentMode . '". Valid options: ' . implode(', ', LeadPaymentMode::values()) . '.';
        }

        // L: Payment Status — must be a valid enum value if provided
        $paymentStatus = data_get($mapped, 'payment_status', '');
        if ($paymentStatus !== '' && ! in_array($paymentStatus, LeadPaymentStatus::values(), true)) {
            $errors[] = 'Invalid payment status "' . $paymentStatus . '". Valid options: ' . implode(', ', LeadPaymentStatus::values()) . '.';
        }

        // M: Status — must be a valid enum value if provided
        $status = data_get($mapped, 'status', '');
        if ($status !== '' && ! in_array($status, LeadStatus::values(), true)) {
            $errors[] = 'Invalid status "' . $status . '". Valid options: ' . implode(', ', LeadStatus::values()) . '.';
        }

        // O: Lead Date — between 2020-01-01 and 2050-12-31 if provided (matches addDateValidation)
        $rawLeadDate = data_get($mapped, 'lead_date', '');
        $leadDate    = $this->parseDate($rawLeadDate);
        if ($rawLeadDate !== '' && $leadDate === null) {
            $errors[] = 'Lead Date is invalid. Expected format: DD/MM/YYYY (e.g. 31/12/2025).';
        } elseif ($leadDate !== null) {
            $dt = new \DateTime($leadDate);
            if ($dt < new \DateTime('2020-01-01') || $dt > new \DateTime('2050-12-31')) {
                $errors[] = 'Lead Date must be between 01/01/2020 and 31/12/2050.';
            }
        }

        // R: Weed Spray — must be a valid enum value if provided
        $weedSpray = data_get($mapped, 'weed_spray', '');
        if ($weedSpray !== '' && ! in_array($weedSpray, LeadWeedSpray::values(), true)) {
            $errors[] = 'Invalid weed spray "' . $weedSpray . '". Valid options: ' . implode(', ', LeadWeedSpray::values()) . '.';
        }

        // V: Latitude — decimal between -90 and 90 if provided
        $lat = data_get($mapped, 'latitude', '');
        if ($lat !== '') {
            $latFloat = filter_var($lat, FILTER_VALIDATE_FLOAT);
            if ($latFloat === false || $latFloat < -90 || $latFloat > 90) {
                $errors[] = 'Latitude must be a decimal number between -90 and 90.';
            }
        }

        // W: Longitude — decimal between -180 and 180 if provided
        $lng = data_get($mapped, 'longitude', '');
        if ($lng !== '') {
            $lngFloat = filter_var($lng, FILTER_VALIDATE_FLOAT);
            if ($lngFloat === false || $lngFloat < -180 || $lngFloat > 180) {
                $errors[] = 'Longitude must be a decimal number between -180 and 180.';
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, string>  $mapped
     * @return array<string, mixed>
     */
    private function buildLeadData(array $mapped): array
    {
        $zoneId = null;
        if (! empty(data_get($mapped, 'zone', ''))) {
            $zoneId = $this->resolveOrCreateByName(Zone::class, data_get($mapped, 'zone'));
        }

        $recurrenceId = null;
        if (! empty(data_get($mapped, 'recurrence', ''))) {
            $recurrenceId = $this->resolveOrCreateByName(Recurrence::class, data_get($mapped, 'recurrence'));
        }

        $equipmentTypeId = null;
        if (! empty(data_get($mapped, 'equipment_type', ''))) {
            $equipmentTypeId = EquipmentType::query()
                ->where('name', 'like', '%' . trim(data_get($mapped, 'equipment_type')) . '%')
                ->value('id');
        }

        $rawSt = data_get($mapped, 'service_types', '');
        if (! empty($rawSt)) {
            $valid      = LeadServiceType::values();
            $validLower = array_combine(array_map('strtolower', $valid), $valid);
            $normalized = implode(', ', array_map(
                fn ($s) => $validLower[strtolower(trim($s))] ?? trim($s),
                explode(',', $rawSt)
            ));
            $serviceTypes = ServiceTypes::parseCsvCell($normalized);
        } else {
            $serviceTypes = [];
        }

        $weedSpray = data_get($mapped, 'weed_spray', '');
        $jobType   = data_get($mapped, 'job_type', '');
        $payMode   = data_get($mapped, 'payment_mode', '');
        $payStatus = data_get($mapped, 'payment_status', '');
        $status    = data_get($mapped, 'status', '');

        return [
            'client_name'            => data_get($mapped, 'client_name'),
            'email'                  => data_get($mapped, 'email'),
            'mobile_number'          => data_get($mapped, 'mobile_number'),
            'address'                => data_get($mapped, 'address'),
            'zone_id'                => $zoneId,
            'service_types'          => $serviceTypes,
            'weed_spray'             => in_array($weedSpray, LeadWeedSpray::values(), true) ? $weedSpray : null,
            'equipment_type_id'      => $equipmentTypeId,
            'recurrence_id'          => $recurrenceId,
            'job_type'               => in_array($jobType, LeadJobType::values(), true) ? $jobType : null,
            'charges'                => data_get($mapped, 'charges'),
            'payment_mode'           => in_array($payMode, LeadPaymentMode::values(), true) ? $payMode : null,
            'payment_status'         => in_array($payStatus, LeadPaymentStatus::values(), true) ? $payStatus : LeadPaymentStatus::PENDING->value,
            'remarks'                => data_get($mapped, 'remarks'),
            'property_details'       => data_get($mapped, 'property_details'),
            'latitude'               => $this->parseCoordinate(data_get($mapped, 'latitude', ''), -90, 90),
            'longitude'              => $this->parseCoordinate(data_get($mapped, 'longitude', ''), -180, 180),
            'lead_date'              => $this->parseDate(data_get($mapped, 'lead_date', '')),
            'lead_time'              => data_get($mapped, 'lead_time'),
            'status'                 => in_array($status, LeadStatus::values(), true) ? $status : LeadStatus::NEW->value,
            'assigned_sales_user_id' => null,
        ];
    }

    /** @param class-string<Model> $modelClass */
    private function resolveOrCreateByName(string $modelClass, string|null $name): ?int
    {
        $name = trim($name);
        if ($name === '' || $name === null) {
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

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            $dt = Carbon::createFromFormat('d/m/Y', $value);

            return $dt ? $dt->format('Y-m-d') : null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)?->format('Y-m-d');
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
