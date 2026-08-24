<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Enums\UserEfficiency;
use App\Enums\UserStatus;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;
use App\Support\MasterCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeds a Gorilla CRM (Firestore) JSON backup into the CRM tables.
 *
 * The export holds two collections:
 *  - crm_data: one document per property. `type` is "Customer" (becomes jobs)
 *    or "Lead" (becomes a lead). `detailedHistory` holds the completed visits.
 *  - mowers: the field staff, imported as users with the Mower role.
 */
class GorillaCrmBackupImport
{
    /** Default password given to every user created by the import. */
    public const DEFAULT_PASSWORD = 'password';

    /** Names that appear in mower fields but are not real people. */
    private const NON_MOWER_NAMES = ['node', 'system', 'admin', 'n/a', 'none'];

    /** Customer-name placeholders in the export; the address identifies the property instead. */
    private const PLACEHOLDER_CUSTOMER_NAMES = ['no name', 'n/a', 'na', 'none', 'unknown', '-'];

    /** Maps the export's free-text service names onto the master catalog. */
    private const SERVICE_NAME_MAP = [
        'mulching' => 'Mulching',
        'sideshoot' => 'Side Shoot',
        'side shoot' => 'Side Shoot',
        'cut and leave' => 'Cut & Leave',
        'cut & leave' => 'Cut & Leave',
        'cut and away' => 'Cut & Away Side Shoot',
        'cut & away' => 'Cut & Away Side Shoot',
        'cut and sideshoot' => 'Cut & Away Side Shoot',
        'cut and away side shoot' => 'Cut & Away Side Shoot',
    ];

    /** Cadences that already exist in the recurrence catalog under another name. */
    private const RECURRENCE_NAME_MAP = [
        '1-Weekly' => 'Weekly',
        '2-Weekly' => 'Bi-Weekly',
    ];

    /** @var array<string, array<string, int>> Resolved master ids keyed by catalog then lowercase name. */
    private array $masterIds = [];

    /** @var array<string, array{created: int, matched: int}> */
    private array $masterCounts = [];

    /** @var array<string, User> Resolved mowers keyed by lowercase name. */
    private array $mowers = [];

    /** @var list<string> */
    private array $warnings = [];

    /** @var array<string, int> */
    private array $counts = [
        'users_created' => 0,
        'users_matched' => 0,
        'leads_created' => 0,
        'leads_skipped' => 0,
        'jobs_created' => 0,
        'history_jobs_created' => 0,
        'jobs_skipped' => 0,
        'assignments_created' => 0,
        'lead_history_skipped' => 0,
    ];

    public function __construct(
        private readonly User $actor,
        private readonly string $mowerEmailDomain,
    ) {}

    /**
     * @return array{
     *     sha256: string,
     *     dry_run: bool,
     *     exported_at: string|null,
     *     records: array{crm_data: int, mowers: int},
     *     counts: array<string, int>,
     *     masters: array<string, array{created: int, matched: int}>,
     *     warnings: list<string>
     * }
     */
    public function import(string $path, bool $dryRun = false): array
    {
        $sha256 = hash_file('sha256', $path);
        $payload = $this->decode($path);

        $crmData = is_array($payload['crm_data'] ?? null) ? $payload['crm_data'] : [];
        $mowers = is_array($payload['mowers'] ?? null) ? $payload['mowers'] : [];

        if ($crmData === [] && $mowers === []) {
            throw new \InvalidArgumentException('The backup contains no "crm_data" or "mowers" records.');
        }

        DB::beginTransaction();

        try {
            $this->seedMowers($mowers, $crmData);

            foreach ($crmData as $record) {
                if (! is_array($record)) {
                    continue;
                }

                if ($this->stringValue($record, ['type']) === 'Lead') {
                    $this->importLead($record);

                    continue;
                }

                $this->importCustomer($record);
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        if (! $dryRun) {
            $this->flushMasterCatalogCaches();
        }

        return [
            'sha256' => (string) $sha256,
            'dry_run' => $dryRun,
            'exported_at' => is_string($payload['timestamp'] ?? null) ? $payload['timestamp'] : null,
            'records' => ['crm_data' => count($crmData), 'mowers' => count($mowers)],
            'counts' => $this->counts,
            'masters' => $this->masterCounts,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $path): array
    {
        $raw = @file_get_contents($path);

        if ($raw === false) {
            throw new \InvalidArgumentException('The uploaded file could not be read.');
        }

        try {
            $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('The uploaded file is not valid JSON: '.$e->getMessage());
        }

        if (! is_array($payload)) {
            throw new \InvalidArgumentException('The backup must be a JSON object.');
        }

        return $payload;
    }

    // ---------------------------------------------------------------------
    // Users
    // ---------------------------------------------------------------------

    /**
     * @param  array<int, mixed>  $mowers
     * @param  array<int, mixed>  $crmData
     */
    private function seedMowers(array $mowers, array $crmData): void
    {
        Role::findOrCreate(CrmRoles::MOWER, CrmPermissions::guard());

        foreach ($mowers as $mower) {
            if (! is_array($mower)) {
                continue;
            }

            $name = $this->cleanMowerName($this->stringValue($mower, ['name']));

            if ($name === null) {
                continue;
            }

            $this->resolveMower($name, [
                'phone' => $this->stringValue($mower, ['phone']),
                'efficiency' => $this->stringValue($mower, ['efficiency']),
                'created_at' => $this->firestoreDate($mower['createdAt'] ?? null),
            ]);
        }

        foreach ($crmData as $record) {
            if (! is_array($record)) {
                continue;
            }

            foreach ($this->mowerNamesIn($record) as $name) {
                $this->resolveMower($name);
            }
        }
    }

    /**
     * Every mower name referenced by a single record (assignment plus visit history).
     *
     * @param  array<string, mixed>  $record
     * @return list<string>
     */
    private function mowerNamesIn(array $record): array
    {
        $names = [
            $this->stringValue($record, ['assignedMowerName']),
            $this->stringValue($record, ['Done By']),
        ];

        foreach ($this->historyEntries($record) as $entry) {
            $names[] = $this->stringValue($entry, ['mower']);
        }

        $cleaned = [];
        foreach ($names as $name) {
            $clean = $this->cleanMowerName($name);
            if ($clean !== null) {
                $cleaned[strtolower($clean)] = $clean;
            }
        }

        return array_values($cleaned);
    }

    /**
     * @param  array{phone?: string|null, efficiency?: string|null, created_at?: Carbon|null}  $attributes
     */
    private function resolveMower(string $name, array $attributes = []): ?User
    {
        $key = strtolower($name);

        if (isset($this->mowers[$key])) {
            return $this->mowers[$key];
        }

        $existing = User::withTrashed()->whereRaw('LOWER(name) = ?', [$key])->first();

        if ($existing) {
            $this->counts['users_matched']++;

            return $this->mowers[$key] = $existing;
        }

        $email = Str::slug($name, '.').'@'.$this->mowerEmailDomain;
        $existingByEmail = User::withTrashed()->where('email', $email)->first();

        if ($existingByEmail) {
            $this->counts['users_matched']++;

            return $this->mowers[$key] = $existingByEmail;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->phone = $this->stringValue($attributes, ['phone']) === null
            ? null
            : Str::limit((string) $attributes['phone'], 30, '');
        $user->efficiency = $this->efficiencyFor($attributes['efficiency'] ?? null);
        $user->status = UserStatus::ACTIVE->value;
        $user->is_active = true;
        $user->password = self::DEFAULT_PASSWORD;
        $user->save();

        if (! empty($attributes['created_at'])) {
            $user->forceFill(['created_at' => $attributes['created_at']])->saveQuietly();
        }

        $user->assignRole(CrmRoles::MOWER);

        $this->counts['users_created']++;

        return $this->mowers[$key] = $user;
    }

    /** Firestore stores efficiency as a percentage; the CRM uses a three-step scale. */
    private function efficiencyFor(?string $raw): string
    {
        $value = trim((string) $raw);

        if ($value === '' || ! is_numeric($value)) {
            return in_array($value, UserEfficiency::values(), true)
                ? $value
                : UserEfficiency::AVERAGE->value;
        }

        return match (true) {
            (float) $value >= 90 => UserEfficiency::GOOD->value,
            (float) $value >= 75 => UserEfficiency::AVERAGE->value,
            default => UserEfficiency::BEGINNER->value,
        };
    }

    private function cleanMowerName(?string $raw): ?string
    {
        $name = trim((string) $raw);

        if ($name === '' || in_array(strtolower($name), self::NON_MOWER_NAMES, true)) {
            return null;
        }

        // The export mixes "HARPREET SINGH", "harpreet singh" and "Harpreet Singh".
        return Str::title(Str::lower($name));
    }

    // ---------------------------------------------------------------------
    // Leads
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $record
     */
    private function importLead(array $record): void
    {
        $address = $this->address($record);

        if ($address === null) {
            $this->warnings[] = 'Skipped a lead without an address (id '.$this->stringValue($record, ['id']).').';
            $this->counts['leads_skipped']++;

            return;
        }

        $leadDate = $this->dateValue($record, ['date', 'Date']);

        $exists = Lead::withTrashed()
            ->where('address', $address)
            ->when($leadDate !== null, fn ($query) => $query->whereDate('lead_date', $leadDate))
            ->exists();

        if ($exists) {
            $this->counts['leads_skipped']++;

            return;
        }

        $converted = (bool) ($record['isConverted'] ?? false);

        $lead = new Lead;
        $lead->forceFill([
            'zone_id' => $this->masterId(MasterCatalog::ZONES, $this->stringValue($record, ['zone', 'Zone'])),
            'client_name' => Str::limit($this->customerName($record, $address), 120, ''),
            'email' => $this->emailValue($record),
            'mobile_number' => $this->phoneValue($record),
            'address' => $address,
            'service_types' => $this->serviceNames($record),
            'weed_spray' => $this->weedSpray($record),
            'equipment_type_id' => $this->equipmentTypeId($record),
            'recurrence_id' => $this->recurrenceId($record),
            'job_type' => $this->jobType($record),
            'charges' => $this->chargesValue($record),
            'payment_mode' => $this->paymentMode($record),
            'property_details' => $this->stringValue($record, ['instructions']),
            'remarks' => $this->remarks($record),
            'status' => $converted ? LeadStatus::WON->value : LeadStatus::NEW->value,
            'latitude' => $this->coordinate($record, 'lat'),
            'longitude' => $this->coordinate($record, 'lng'),
            'lead_date' => $leadDate,
            'converted_at' => $converted ? $this->firestoreDate($record['convertedAt'] ?? null) : null,
            'deleted_at' => $this->softDeletedAt($record),
        ]);

        $this->applyTimestamps($lead, $record);
        $lead->save();

        $this->counts['leads_created']++;

        // Leads have no job rows yet, so their visit history cannot be attached anywhere.
        $this->counts['lead_history_skipped'] += count($this->historyEntries($record));
    }

    // ---------------------------------------------------------------------
    // Customers -> jobs
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $record
     */
    private function importCustomer(array $record): void
    {
        $address = $this->address($record);

        if ($address === null) {
            $this->warnings[] = 'Skipped a customer without an address (id '.$this->stringValue($record, ['id']).').';
            $this->counts['jobs_skipped']++;

            return;
        }

        $shared = $this->sharedJobAttributes($record, $address);

        foreach ($this->historyEntries($record) as $entry) {
            $this->importHistoryJob($shared, $entry);
        }

        $scheduledDate = $this->dateValue($record, ['date', 'Date']);

        if ($scheduledDate === null) {
            $this->warnings[] = 'No upcoming date for "'.$shared['customer_name'].'" ('.$address.') — only its visit history was imported.';

            return;
        }

        if ($this->jobExists($address, $scheduledDate)) {
            $this->counts['jobs_skipped']++;

            return;
        }

        $mower = $this->mowerFor($this->stringValue($record, ['assignedMowerName', 'Done By']));
        $status = $this->workflowStatus($record);

        $job = new Job;
        $job->forceFill($shared + [
            'scheduled_date' => $scheduledDate,
            'status' => $status,
            'payment_status' => JobOperationalPaymentStatus::PENDING->value,
            'payment_pending_reason' => $status === JobWorkflowStatus::HOLD->value
                ? $this->stringValue($record, ['tempRemarks'])
                : null,
            'done_by_user_id' => $mower?->id,
            'rescheduled_at' => $this->lastRescheduledAt($record),
            'deleted_at' => $this->softDeletedAt($record),
        ]);

        $this->applyTimestamps($job, $record);
        $job->save();

        $this->counts['jobs_created']++;

        $this->attachMower($job, $mower, $status);
    }

    /**
     * A completed visit from `detailedHistory` becomes its own job row.
     *
     * @param  array<string, mixed>  $shared
     * @param  array<string, mixed>  $entry
     */
    private function importHistoryJob(array $shared, array $entry): void
    {
        $date = $this->dateValue($entry, ['date']);

        if ($date === null) {
            return;
        }

        if ($this->jobExists($shared['client_address'], $date)) {
            $this->counts['jobs_skipped']++;

            return;
        }

        $mower = $this->mowerFor($this->stringValue($entry, ['mower']));
        // A recorded verifiedAmount means the office reconciled the payment, even
        // when the older paymentVerified flag is missing.
        $verified = (bool) ($entry['paymentVerified'] ?? false)
            || $this->numericValue($entry['verifiedAmount'] ?? null) !== null;

        $job = new Job;
        $job->forceFill($shared + [
            'scheduled_date' => $date,
            'status' => JobWorkflowStatus::COMPLETED->value,
            'charges' => $this->numericValue($entry['amount'] ?? null) ?? $shared['charges'],
            'payment_mode' => $this->historyPaymentMode($entry) ?? $shared['payment_mode'],
            'payment_status' => $this->historyPaymentStatus($entry),
            'internal_notes' => $this->historyNotes($entry),
            'done_by_user_id' => $mower?->id,
            'verified_at' => $verified ? $this->dateValue($entry, ['receivedDate']) ?? $date : null,
            'verified_by' => $verified ? $this->actor->id : null,
        ]);

        $job->created_at = $date;
        $job->updated_at = $date;
        $job->save();

        $this->counts['history_jobs_created']++;

        $this->attachMower($job, $mower, JobWorkflowStatus::COMPLETED->value);
    }

    /**
     * Property and contact fields shared by every job built from one customer record.
     *
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function sharedJobAttributes(array $record, string $address): array
    {
        return [
            'customer_name' => Str::limit($this->customerName($record, $address), 120, ''),
            'email' => $this->emailValue($record),
            'phone' => $this->phoneValue($record),
            'client_address' => $address,
            'weed_spray' => $this->weedSpray($record),
            'job_type' => $this->jobType($record),
            'zone_id' => $this->masterId(MasterCatalog::ZONES, $this->stringValue($record, ['zone', 'Zone'])),
            'equipment_type_id' => $this->equipmentTypeId($record),
            'accounting_level_id' => $this->accountingLevelId($record),
            'client_rating_id' => $this->clientRatingId($record),
            'job_level_id' => $this->masterId(MasterCatalog::JOB_LEVELS, $this->stringValue($record, ['jobLevel', 'Job Level'])),
            'recurrence_id' => $this->recurrenceId($record),
            'latitude' => $this->coordinate($record, 'lat'),
            'longitude' => $this->coordinate($record, 'lng'),
            'required_services' => $this->serviceNames($record),
            'is_recurring' => $this->stringValue($record, ['jobType', 'Job Type']) === 'Regular',
            'customer_type' => $this->customerType($record),
            'site_instructions' => $this->stringValue($record, ['instructions']),
            'special_remarks' => $this->remarks($record),
            'payment_mode' => $this->paymentMode($record),
            'charges' => $this->chargesValue($record),
            'created_by' => $this->actor->id,
        ];
    }

    private function jobExists(string $address, string $date): bool
    {
        return Job::withTrashed()
            ->where('client_address', $address)
            ->whereDate('scheduled_date', $date)
            ->exists();
    }

    private function attachMower(Job $job, ?User $mower, string $status): void
    {
        if (! $mower) {
            return;
        }

        $job->assignedEmployees()->syncWithoutDetaching([
            $mower->id => [
                'assignment_date' => $job->scheduled_date,
                'assignment_status' => $status,
                'incentive_percentage' => $mower->incentive_percentage ?? 0,
            ],
        ]);

        $this->counts['assignments_created']++;
    }

    private function mowerFor(?string $name): ?User
    {
        $clean = $this->cleanMowerName($name);

        return $clean === null ? null : ($this->mowers[strtolower($clean)] ?? null);
    }

    // ---------------------------------------------------------------------
    // Master catalog
    // ---------------------------------------------------------------------

    private function masterId(string $catalog, ?string $name): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $key = strtolower($name);

        if (isset($this->masterIds[$catalog][$key])) {
            return $this->masterIds[$catalog][$key];
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = MasterCatalog::modelClass($catalog);

        /** @var Model $model */
        $model = $modelClass::query()->firstOrCreate(
            ['name' => $name],
            ['is_active' => true, 'sort_order' => (int) $modelClass::query()->max('sort_order') + 1],
        );

        $bucket = $model->wasRecentlyCreated ? 'created' : 'matched';
        $this->masterCounts[$catalog] ??= ['created' => 0, 'matched' => 0];
        $this->masterCounts[$catalog][$bucket]++;

        return $this->masterIds[$catalog][$key] = (int) $model->getKey();
    }

    private function flushMasterCatalogCaches(): void
    {
        foreach (array_keys($this->masterCounts) as $catalog) {
            MasterCatalog::forgetCache($catalog);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     * @return list<string>
     */
    private function serviceNames(array $record): array
    {
        $raw = is_array($record['services'] ?? null) ? $record['services'] : [];

        foreach (['serviceType', 'Service type'] as $key) {
            $value = $this->stringValue($record, [$key]);
            if ($value !== null) {
                $raw = array_merge($raw, explode(',', $value));
            }
        }

        $names = [];
        foreach ($raw as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $name = trim((string) $item);
            if ($name === '') {
                continue;
            }

            $canonical = self::SERVICE_NAME_MAP[strtolower($name)] ?? $name;
            $this->masterId(MasterCatalog::SERVICE_TYPES, $canonical);
            $names[$canonical] = $canonical;
        }

        return array_values($names);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function equipmentTypeId(array $record): ?int
    {
        return $this->masterId(
            MasterCatalog::EQUIPMENT_TYPES,
            $this->stringValue($record, ['assignedEquipment', 'Assigned Equipment', 'equipment'])
        );
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function recurrenceId(array $record): ?int
    {
        $raw = $this->stringValue($record, ['frequency', 'Frequency']);

        if ($raw === null) {
            return $this->masterId(MasterCatalog::RECURRENCES, 'One-Time');
        }

        // "3", "3 Weekly" and "3-Weekly" all mean the same cadence.
        $name = is_numeric($raw)
            ? ((int) $raw).'-Weekly'
            : (string) preg_replace('/\s+/', '-', $raw);

        return $this->masterId(MasterCatalog::RECURRENCES, self::RECURRENCE_NAME_MAP[$name] ?? $name);
    }

    /**
     * Firestore writes accounting levels as "*C" / "**I"; the catalog uses stars.
     *
     * @param  array<string, mixed>  $record
     */
    private function accountingLevelId(array $record): ?int
    {
        $raw = $this->stringValue($record, ['accountingLevel', 'Accounting Level']);

        if ($raw === null) {
            return null;
        }

        $stars = strspn($raw, '*');
        $suffix = trim(substr($raw, $stars));
        $name = str_repeat('⭐', max($stars, 1)).($suffix === '' ? '' : ' '.$suffix);

        return $this->masterId(MasterCatalog::ACCOUNTING_LEVELS, $name);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function clientRatingId(array $record): ?int
    {
        $raw = $this->stringValue($record, ['rating', 'Customer Rating']);

        if ($raw === null) {
            return null;
        }

        // "Platinum Client", "Platinum Customer" and "Platinum" are one rating.
        $name = trim((string) preg_replace('/\s+(client|customer)$/i', '', $raw));

        return $this->masterId(MasterCatalog::CLIENT_RATINGS, $name === '' ? $raw : $name);
    }

    // ---------------------------------------------------------------------
    // Field mapping
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $record
     * @return list<array<string, mixed>>
     */
    private function historyEntries(array $record): array
    {
        $history = $record['detailedHistory'] ?? null;

        if (! is_array($history)) {
            return [];
        }

        return array_values(array_filter($history, 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function address(array $record): ?string
    {
        $address = $this->stringValue($record, ['address', 'Address']);

        return $address === null ? null : Str::limit($address, 255, '');
    }

    /**
     * The export never carries a real contact name — every record is "No Name" or
     * blank — so the address doubles as the customer name.
     *
     * @param  array<string, mixed>  $record
     */
    private function customerName(array $record, string $address): string
    {
        $name = $this->stringValue($record, ['name']);

        if ($name === null || in_array(strtolower($name), self::PLACEHOLDER_CUSTOMER_NAMES, true)) {
            return $address;
        }

        return $name;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function emailValue(array $record): ?string
    {
        $email = $this->stringValue($record, ['email', 'Email']);

        return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function phoneValue(array $record): ?string
    {
        $phone = $this->stringValue($record, ['phone', 'Mobile no.']);

        return $phone === null ? null : Str::limit($phone, 30, '');
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function chargesValue(array $record): ?float
    {
        return $this->numericValue($record['charges'] ?? null) ?? $this->numericValue($record['Charges'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function weedSpray(array $record): ?string
    {
        $value = $this->stringValue($record, ['weedSpray', 'Weed Spray']);

        return $value !== null && in_array($value, LeadWeedSpray::values(), true) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function jobType(array $record): ?string
    {
        return match ($this->stringValue($record, ['jobType', 'Job Type'])) {
            'Regular' => LeadJobType::REGULAR->value,
            'On Call', 'On call' => LeadJobType::ON_CALL->value,
            'One Off' => LeadJobType::NEW->value,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function paymentMode(array $record): string
    {
        return match ($this->stringValue($record, ['paymentMode', 'Payment Mode'])) {
            'Cash' => JobOperationalPaymentMode::CASH->value,
            'Both' => JobOperationalPaymentMode::BOTH->value,
            default => JobOperationalPaymentMode::ONLINE->value,
        };
    }

    /**
     * The export's "Medium" has no CRM equivalent, so it falls back to "Don't Know".
     *
     * @param  array<string, mixed>  $record
     */
    private function customerType(array $record): ?string
    {
        $value = $this->stringValue($record, ['customerType', 'Customer Type']);

        if ($value === null) {
            return null;
        }

        return in_array($value, JobCustomerType::values(), true)
            ? $value
            : JobCustomerType::DONT_KNOW->value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function workflowStatus(array $record): string
    {
        return match ($this->stringValue($record, ['status'])) {
            'Hold' => JobWorkflowStatus::HOLD->value,
            'Completed' => JobWorkflowStatus::COMPLETED->value,
            default => JobWorkflowStatus::PENDING->value,
        };
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function remarks(array $record): ?string
    {
        $parts = array_filter([
            $this->stringValue($record, ['Remarks']),
            $this->stringValue($record, ['tempRemarks']),
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function historyPaymentStatus(array $entry): string
    {
        $received = $this->stringValue($entry, ['status']) === 'Received'
            || $this->stringValue($entry, ['paymentStatus']) === 'Paid';

        return $received
            ? JobOperationalPaymentStatus::RECEIVED->value
            : JobOperationalPaymentStatus::PENDING->value;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function historyPaymentMode(array $entry): ?string
    {
        return match ($this->stringValue($entry, ['mode'])) {
            'Cash' => JobOperationalPaymentMode::CASH->value,
            'Online' => JobOperationalPaymentMode::ONLINE->value,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function historyNotes(array $entry): ?string
    {
        $parts = array_filter([
            $this->stringValue($entry, ['remarks']),
            $this->stringValue($entry, ['notes']),
            $this->stringValue($entry, ['instructions']),
            $this->verifiedAmountNote($entry),
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }

    /**
     * The export's `verifiedAmount` is the reconciled figure for a settled payment,
     * not an outstanding balance, so it has no `payment_status` equivalent. Only the
     * rare mismatch against `amount` is worth carrying over, as a note on the job.
     *
     * @param  array<string, mixed>  $entry
     */
    private function verifiedAmountNote(array $entry): ?string
    {
        $verified = $this->numericValue($entry['verifiedAmount'] ?? null);
        $amount = $this->numericValue($entry['amount'] ?? null);

        if ($verified === null || $amount === null || $verified === $amount) {
            return null;
        }

        return sprintf(
            'Verified amount %s does not match the charged %s (%s%s).',
            number_format($verified, 2),
            number_format($amount, 2),
            $verified > $amount ? 'over by ' : 'short by ',
            number_format(abs($verified - $amount), 2),
        );
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function lastRescheduledAt(array $record): ?Carbon
    {
        $history = $record['rescheduleHistory'] ?? null;

        if (! is_array($history) || $history === []) {
            return null;
        }

        $last = end($history);

        if (! is_array($last) || ! is_string($last['at'] ?? null)) {
            return null;
        }

        return $this->parseDate($last['at']);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function softDeletedAt(array $record): ?Carbon
    {
        if (empty($record['isDeleted'])) {
            return null;
        }

        return $this->firestoreDate($record['deletedAt'] ?? null) ?? now();
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function coordinate(array $record, string $key): ?float
    {
        $value = $this->numericValue($record[$key] ?? null);

        return $value === null || $value === 0.0 ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function applyTimestamps(Model $model, array $record): void
    {
        $model->created_at = $this->firestoreDate($record['createdAt'] ?? null) ?? now();
        $model->updated_at = $this->firestoreDate($record['updatedAt'] ?? null) ?? $model->created_at;
    }

    private function firestoreDate(mixed $value): ?Carbon
    {
        if (is_array($value) && isset($value['seconds'])) {
            return Carbon::createFromTimestampUTC((int) $value['seconds']);
        }

        return is_string($value) ? $this->parseDate($value) : null;
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  list<string>  $keys
     */
    private function dateValue(array $source, array $keys): ?string
    {
        $raw = $this->stringValue($source, $keys);

        return $raw === null ? null : $this->parseDate($raw)?->toDateString();
    }

    private function parseDate(string $raw): ?Carbon
    {
        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    private function numericValue(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        return is_string($value) && is_numeric(trim($value)) ? (float) trim($value) : null;
    }

    /**
     * First non-empty scalar among the given keys.
     *
     * @param  array<string, mixed>  $source
     * @param  list<string>  $keys
     */
    private function stringValue(array $source, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $source[$key] ?? null;

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
