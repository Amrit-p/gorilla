<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkNotificationReadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'notification_ids' => ['nullable', 'array'],
            'notification_ids.*' => [
                'string',
                Rule::exists('notifications', 'id')->where(function ($query): void {
                    $query->where('notifiable_type', User::class)
                        ->where('notifiable_id', $this->user()->id);
                }),
            ],
        ];
    }
}
