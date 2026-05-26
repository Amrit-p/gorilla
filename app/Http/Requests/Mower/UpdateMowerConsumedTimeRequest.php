<?php

namespace App\Http\Requests\Mower;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMowerConsumedTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'consumed_time_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }
}
