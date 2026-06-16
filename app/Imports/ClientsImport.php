<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadWeedSpray;
use App\Exports\ClientsExport;
use App\Jobs\GeocodeJobAddressJob;
use App\Models\AccountingLevel;
use App\Models\Client;
use App\Models\EquipmentType;
use App\Models\Job;
use App\Models\JobLevel;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Services\ClientManagementService;
use App\Support\ServiceTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ClientsImport
{
    public function __construct(
        private readonly User $actor,
        private readonly ClientManagementService $service,
        private readonly bool $createJobs = false,
    ) {}

    /** Headers that cannot be auto-derived from the column name, or must be skipped (null). */
    private const FIELD_OVERRIDES = [
        '#' => null,
        'customer id' => null,
        'client type' => null,
        'created at' => null,
        'charges ($)' => 'charges',
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
            array_column(ClientsExport::COLUMNS, 'header'),
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
        $imported = 0;
        $importedRows = [];
        $failures = [];
        $duplicates = [];

        foreach (array_slice($rows, 4) as $rowIndex => $row) {
            $rowNumber = $rowIndex + 6;

            $mapped = [];
            foreach ($normalizedHeaders as $idx => $field) {
                if ($field !== null) {
                    $mapped[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
                }
            }

            $email = data_get($mapped, 'email', '');
            $address = data_get($mapped, 'address', '');
            $identifier = $email ?: ($address ?: "Row {$rowNumber}");

            if ($email !== '' && Client::query()->where('email', $email)->exists()) {
                $duplicates[] = [
                    'row' => $rowNumber,
                    'identifier' => $identifier,
                    'reason' => ["A customer with email \"{$email}\" already exists."],
                ];

                continue;
            }

            $validationErrors = $this->validateMappedRow($mapped);
            if (! empty($validationErrors)) {
                $failures[] = [
                    'row' => $rowNumber,
                    'identifier' => $identifier,
                    'reason' => $validationErrors,
                ];

                continue;
            }

            try {
                DB::transaction(function () use ($mapped, $rowNumber, $identifier, &$imported, &$importedRows): void {
                    $data = $this->buildClientData($mapped);
                    $client = $this->service->createClient($this->actor, $data);

                    if ($this->createJobs && $client->recurrence_id) {
                        $recurrence = Recurrence::query()->find($client->recurrence_id);
                        $nextDate = $recurrence?->resolve(now());

                        if ($nextDate !== null) {
                            $job = Job::create([
                                'client_id' => $client->id,
                                'lead_id' => $client->lead_id,
                                'zone_id' => $client->zone_id,
                                'equipment_type_id' => $client->equipment_type_id,
                                'job_level_id' => $client->job_level_id,
                                'recurrence_id' => $client->recurrence_id,
                                'client_address' => $client->address,
                                'latitude' => $client->latitude,
                                'longitude' => $client->longitude,
                                'scheduled_date' => $nextDate->toDateString(),
                                'required_services' => $client->service_types,
                                'is_recurring' => true,
                                'status' => JobWorkflowStatus::PENDING->value,
                                'customer_type' => $client->customer_type,
                                'payment_mode' => $client->payment_mode,
                                'payment_status' => $client->payment_status,
                                'charges' => $client->charges,
                                'created_by' => $this->actor->id,
                            ]);
                            GeocodeJobAddressJob::dispatch($job->id);
                        }
                    }

                    $imported++;
                    $importedRows[] = ['row' => $rowNumber, 'identifier' => $identifier];
                });
            } catch (\Throwable $e) {
                report($e);
                $failures[] = [
                    'row' => $rowNumber,
                    'identifier' => $identifier,
                    'reason' => [$e->getMessage()],
                ];
            }
        }

        return [
            'imported' => $imported,
            'imported_rows' => $importedRows,
            'failed' => count($failures),
            'failures' => $failures,
            'duplicated' => count($duplicates),
            'duplicates' => $duplicates,
        ];
    }

    /** @return array<string, string|null> */
    private static function headerMap(): array
    {
        if (self::$headerMapCache !== null) {
            return self::$headerMapCache;
        }

        $map = [];
        foreach (ClientsExport::COLUMNS as $def) {
            $key = strtolower($def['header']);
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
        $map = self::headerMap();
        $normalizedUploaded = array_map(fn ($h) => strtolower(trim($h)), $rawHeaders);

        $missing = array_values(array_filter(
            self::importColumns(),
            fn ($h) => ! in_array(strtolower($h), $normalizedUploaded, true)
        ));

        $issues = [];
        if (! empty($missing)) {
            $issues[] = 'missing columns: '.implode(', ', $missing);
        }

        if (! empty($issues)) {
            throw new \InvalidArgumentException('Invalid file format — '.implode('; ', $issues).'. Download the sample file to see the expected format.');
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
     * @param  array<string, string>  $mapped
     * @return list<string>
     */
    private function validateMappedRow(array $mapped): array
    {
        $errors = [];

        // F: Address — required
        if (data_get($mapped, 'address', '') === '') {
            $errors[] = 'Address is required.';
        }

        // C: Name — optional, max 255 chars
        $name = data_get($mapped, 'name', '');
        if ($name !== '' && strlen($name) > 255) {
            $errors[] = 'Name must not exceed 255 characters.';
        }

        // D: Email — optional, basic format check
        $email = data_get($mapped, 'email', '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address (e.g. user@example.com).';
        }

        // E: Phone — optional, max 30 chars
        if (strlen(data_get($mapped, 'phone', '')) > 30) {
            $errors[] = 'Phone number must not exceed 30 characters.';
        }

        // J: Service Types — required; validate each comma-separated value
        $rawServiceTypes = data_get($mapped, 'service_types', '');
        if ($rawServiceTypes === '') {
            $errors[] = 'Service types are required.';
        } else {
            $given = array_map('trim', explode(',', $rawServiceTypes));
            $valid = ServiceTypes::all();
            $validLower = array_map('strtolower', $valid);
            $invalid = array_filter($given, fn ($g) => ! in_array(strtolower($g), $validLower, true));
            if (! empty($invalid)) {
                $errors[] = 'Invalid service type(s): '.implode(', ', $invalid).'. Valid options: '.implode(', ', $valid).'.';
            }
        }

        // K: Equipment Type — must match a known name if provided
        $equipmentName = data_get($mapped, 'equipment_type', '');
        if ($equipmentName !== '') {
            $exists = EquipmentType::query()->where('name', 'like', '%'.trim($equipmentName).'%')->exists();
            if (! $exists) {
                $errors[] = "Equipment type \"{$equipmentName}\" was not found.";
            }
        }

        // L: Job Type — required, must be a valid enum value
        $jobType = data_get($mapped, 'job_type', '');
        if ($jobType === '') {
            $errors[] = 'Job type is required.';
        } elseif (! in_array($jobType, LeadJobType::values(), true)) {
            $errors[] = 'Invalid job type "'.$jobType.'". Valid options: '.implode(', ', LeadJobType::values()).'.';
        }

        // M: Charges — decimal >= 0 if provided
        $charges = data_get($mapped, 'charges', '');
        if ($charges !== '') {
            $chargesFloat = filter_var($charges, FILTER_VALIDATE_FLOAT);
            if ($chargesFloat === false || $chargesFloat < 0) {
                $errors[] = 'Charges must be a number greater than or equal to 0.';
            }
        }

        // N: Payment Mode — required, must be a valid enum value
        $paymentMode = data_get($mapped, 'payment_mode', '');
        if ($paymentMode === '') {
            $errors[] = 'Payment mode is required.';
        } elseif (! in_array($paymentMode, LeadPaymentMode::values(), true)) {
            $errors[] = 'Invalid payment mode "'.$paymentMode.'". Valid options: '.implode(', ', LeadPaymentMode::values()).'.';
        }

        // O: Payment Status — optional, must be a valid enum value
        $paymentStatus = data_get($mapped, 'payment_status', '');
        if ($paymentStatus !== '' && ! in_array($paymentStatus, ClientPaymentStatus::values(), true)) {
            $errors[] = 'Invalid payment status "'.$paymentStatus.'". Valid options: '.implode(', ', ClientPaymentStatus::values()).'.';
        }

        // P: Customer Type — required, must be a valid enum value
        $customerType = data_get($mapped, 'customer_type', '');
        if ($customerType === '') {
            $errors[] = 'Customer type is required.';
        } elseif (! in_array($customerType, ClientCustomerType::values(), true)) {
            $errors[] = 'Invalid customer type "'.$customerType.'". Valid options: '.implode(', ', ClientCustomerType::values()).'.';
        }

        // R: Weed Spray — required, must be a valid enum value
        $weedSpray = data_get($mapped, 'weed_spray', '');
        if ($weedSpray === '') {
            $errors[] = 'Weed spray is required.';
        } elseif (! in_array($weedSpray, LeadWeedSpray::values(), true)) {
            $errors[] = 'Invalid weed spray "'.$weedSpray.'". Valid options: '.implode(', ', LeadWeedSpray::values()).'.';
        }

        // S: Recurrence — required, must match an existing active recurrence
        $recurrenceName = data_get($mapped, 'recurrence', '');
        if ($recurrenceName === '') {
            $errors[] = 'Recurrence is required.';
        } else {
            $exists = Recurrence::query()
                ->where('is_active', true)
                ->where('name', 'like', '%'.trim($recurrenceName).'%')
                ->exists();
            if (! $exists) {
                $errors[] = "Recurrence \"{$recurrenceName}\" was not found or is inactive.";
            }
        }

        // W: Latitude — decimal between -90 and 90
        $lat = data_get($mapped, 'latitude', '');
        if ($lat !== '') {
            $latFloat = filter_var($lat, FILTER_VALIDATE_FLOAT);
            if ($latFloat === false || $latFloat < -90 || $latFloat > 90) {
                $errors[] = 'Latitude must be a decimal number between -90 and 90.';
            }
        }

        // X: Longitude — decimal between -180 and 180
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
    private function buildClientData(array $mapped): array
    {
        $zoneId = null;
        if (! empty(data_get($mapped, 'zone', ''))) {
            $zoneId = Zone::query()
                ->where('is_active', true)
                ->where('name', 'like', '%'.trim(data_get($mapped, 'zone')).'%')
                ->value('id');
        }

        $accountingLevelId = null;
        if (! empty(data_get($mapped, 'accounting_level', ''))) {
            $accountingLevelId = $this->resolveOrCreateByName(AccountingLevel::class, data_get($mapped, 'accounting_level'));
        }

        $jobLevelId = null;
        if (! empty(data_get($mapped, 'job_level', ''))) {
            $jobLevelId = $this->resolveOrCreateByName(JobLevel::class, data_get($mapped, 'job_level'));
        }

        $equipmentTypeId = null;
        if (! empty(data_get($mapped, 'equipment_type', ''))) {
            $equipmentTypeId = EquipmentType::query()
                ->where('name', 'like', '%'.trim(data_get($mapped, 'equipment_type')).'%')
                ->value('id');
        }

        $recurrenceId = null;
        if (! empty(data_get($mapped, 'recurrence', ''))) {
            $recurrenceId = Recurrence::query()
                ->where('is_active', true)
                ->where('name', 'like', '%'.trim(data_get($mapped, 'recurrence')).'%')
                ->value('id');
        }

        $rawSt = data_get($mapped, 'service_types', '');
        if (! empty($rawSt)) {
            $valid = ServiceTypes::all();
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
        $jobType = data_get($mapped, 'job_type', '');
        $payMode = data_get($mapped, 'payment_mode', '');
        $payStatus = data_get($mapped, 'payment_status', '');
        $customerType = data_get($mapped, 'customer_type', '');

        return [
            'name' => data_get($mapped, 'name'),
            'email' => data_get($mapped, 'email'),
            'phone' => data_get($mapped, 'phone'),
            'address' => data_get($mapped, 'address'),
            'zone_id' => $zoneId,
            'accounting_level_id' => $accountingLevelId,
            'job_level_id' => $jobLevelId,
            'service_types' => $serviceTypes,
            'equipment_type_id' => $equipmentTypeId,
            'job_type' => in_array($jobType, LeadJobType::values(), true) ? $jobType : LeadJobType::REGULAR->value,
            'charges' => data_get($mapped, 'charges'),
            'payment_mode' => in_array($payMode, LeadPaymentMode::values(), true) ? $payMode : null,
            'payment_status' => in_array($payStatus, ClientPaymentStatus::values(), true) ? $payStatus : ClientPaymentStatus::PENDING->value,
            'customer_type' => in_array($customerType, ClientCustomerType::values(), true) ? $customerType : ClientCustomerType::DONT_KNOW->value,
            'weed_spray' => in_array($weedSpray, LeadWeedSpray::values(), true) ? $weedSpray : null,
            'recurrence_id' => $recurrenceId,
            'property_details' => data_get($mapped, 'property_details'),
            'special_remarks' => data_get($mapped, 'special_remarks'),
            'notes' => data_get($mapped, 'notes'),
            'latitude' => $this->parseCoordinate(data_get($mapped, 'latitude', ''), -90, 90),
            'longitude' => $this->parseCoordinate(data_get($mapped, 'longitude', ''), -180, 180),
        ];
    }

    /** @param class-string<Model> $modelClass */
    private function resolveOrCreateByName(string $modelClass, ?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $record = $modelClass::query()
            ->where('name', 'like', '%'.$name.'%')
            ->first();

        if ($record) {
            return $record->id;
        }

        return $modelClass::create([
            'name' => $name,
            'is_active' => true,
            'sort_order' => 0,
        ])->id;
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
