<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Any authenticated user may save their dashboard preferences.
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'in:light,dark'],
            'compact_cards' => ['required', 'boolean'],
            'show_activity_timeline' => ['required', 'boolean'],
        ];
    }
}
