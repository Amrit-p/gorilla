<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\JobCustomerType;
use App\Enums\JobOperationalPaymentMode;
use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobParkingStatus;
use App\Enums\JobWorkflowStatus;
use App\Models\User;
use App\Support\CrmRoles;
use App\Support\EquipmentTypes;
use App\Support\ServiceTypes;
use Illuminate\Validation\Rule;

trait ValidatesJobOperational
{
    /**
     * @return array<string, array<int, mixed>|string>
     */
    protected function jobOperationalRules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'recurrence_id' => ['required', 'exists:recurrences,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'equipment_type_id' => EquipmentTypes::idRules(),
            'job_level_id' => ['nullable', 'exists:job_levels,id'],
            'client_address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'required_services' => ['required', 'array', 'min:1'],
            'required_services.*' => ServiceTypes::itemRules(),
            'parking_status' => ['nullable', Rule::in(JobParkingStatus::values())],
            'customer_type' => ['nullable', Rule::in(JobCustomerType::values())],
            'pet_warning' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
            'done_by_user_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value || self::userIsActiveMower((int) $value)) {
                        return;
                    }
                    $fail('The primary mower must be an active user with the Mower role.');
                },
            ],
            'payment_mode' => ['required', Rule::in(JobOperationalPaymentMode::values())],
            'payment_status' => ['nullable', Rule::in(JobOperationalPaymentStatus::values())],
            'payment_pending_reason' => [
                'nullable',
                'required_if:payment_status,'.JobOperationalPaymentStatus::PENDING->value,
                'string',
                'max:255',
            ],
            'charges' => ['nullable', 'numeric', 'min:0'],
            'incentive_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'special_remarks' => ['nullable', 'string'],
            'site_instructions' => ['nullable', 'string'],
            'employee_ids' => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value) || $value === []) {
                        return;
                    }
                    $ids = array_values(array_unique(array_map('intval', $value)));
                    $mowerCount = User::query()
                        ->role(CrmRoles::MOWER)
                        ->where('is_active', true)
                        ->whereIn('id', $ids)
                        ->count();
                    if ($mowerCount !== count($ids)) {
                        $fail('Jobs can only be assigned to active mowers.');
                    }
                },
            ],
            'employee_ids.*' => ['integer'],
            'status' => ['nullable', Rule::in(JobWorkflowStatus::values())],
        ];
    }

    protected static function userIsActiveMower(int $userId): bool
    {
        return User::query()
            ->role(CrmRoles::MOWER)
            ->where('is_active', true)
            ->whereKey($userId)
            ->exists();
    }
}
