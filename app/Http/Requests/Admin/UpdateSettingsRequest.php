<?php

namespace App\Http\Requests\Admin;

use App\Support\CrmPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can(CrmPermissions::MANAGE_USERS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        $dbMailOn = fn (): bool => $this->boolean('mail_use_database');

        return [
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:120'],
            'site_logo_initial' => ['nullable', 'string', 'max:2'],
            'site_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
            'remove_site_logo' => ['sometimes', 'boolean'],
            'company_name' => ['required', 'string', 'max:120'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_email' => ['nullable', 'email'],
            'default_timezone' => ['required', 'timezone'],
            'map_provider' => ['required', 'in:mapbox,google'],
            'google_maps_api_key' => ['nullable', 'string', 'max:255'],
            'notification_email_enabled' => ['required', 'boolean'],
            'notification_in_app_enabled' => ['required', 'boolean'],
            'default_currency' => ['required', 'string', 'max:10'],

            'seo_meta_title' => ['nullable', 'string', 'max:120'],
            'seo_meta_description' => ['nullable', 'string', 'max:320'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:500'],
            'seo_og_title' => ['nullable', 'string', 'max:120'],
            'seo_og_description' => ['nullable', 'string', 'max:320'],
            'seo_robots_noindex' => ['required', 'boolean'],

            'mail_use_database' => ['required', 'boolean'],
            'mail_mailer' => [
                Rule::requiredIf($dbMailOn),
                'nullable',
                'in:smtp,log,array',
            ],
            'mail_host' => [
                Rule::requiredIf(fn (): bool => $dbMailOn() && $this->input('mail_mailer') === 'smtp'),
                'nullable',
                'string',
                'max:255',
            ],
            'mail_port' => [
                Rule::requiredIf(fn (): bool => $dbMailOn() && $this->input('mail_mailer') === 'smtp'),
                'nullable',
                'integer',
                'min:1',
                'max:65535',
            ],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'mail_from_address' => [
                Rule::requiredIf($dbMailOn),
                'nullable',
                'email',
                'max:255',
            ],
            'mail_from_name' => ['nullable', 'string', 'max:120'],

            'backup_schedule' => ['nullable', 'in:weekly,biweekly,monthly'],
            'backup_disk' => ['nullable', 'in:local,gcs'],
            'backup_gcs_bucket' => ['nullable', 'string', 'max:255'],
            'backup_gcs_project' => ['nullable', 'string', 'max:255'],
            'backup_gcs_key_file' => ['nullable', 'string', 'max:500'],
            'backup_mysqldump_path' => ['nullable', 'string', 'max:500'],
        ];
    }
}
