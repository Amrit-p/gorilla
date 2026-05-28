<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;

use App\Http\Requests\Admin\Concerns\ValidatesClientIntake;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    use ValidatesClientIntake;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_CUSTOMERS);
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return $this->clientIntakeRules();
    }

}
