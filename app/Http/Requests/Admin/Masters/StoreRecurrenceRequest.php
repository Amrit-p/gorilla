<?php

namespace App\Http\Requests\Admin\Masters;

use App\Models\Recurrence;
use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class StoreRecurrenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_MASTERS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:120', Rule::unique('recurrences', 'name'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    try {
                        Recurrence::parseIntervalDays((string) $value);
                    } catch (InvalidArgumentException) {
                        $fail('Could not parse this recurrence pattern. Try formats like "Weekly", "Bi-Weekly", "Monthly", "Quarterly", "One-Time", or "3 weeks".');
                    }
                },
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
