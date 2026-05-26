<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * Applies mail-related keys from the settings table onto Laravel's mail config.
 * When disabled, the app keeps using .env / config files (default for local dev).
 */
class MailSettingsRegistrar
{
    /**
     * Call during application bootstrap (after config is loaded).
     */
    public static function applyFromDatabase(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        /** @var array<string, string|null> $row */
        $row = Setting::query()->pluck('value', 'key')->all();

        if (($row['mail_use_database'] ?? '0') !== '1') {
            return;
        }

        $mailer = $row['mail_mailer'] ?? 'smtp';
        config(['mail.default' => $mailer]);

        if ($mailer === 'smtp') {
            $port = (int) ($row['mail_port'] ?? 587);
            $enc = $row['mail_encryption'] ?? '';
            // Symfony Esmtp: smtps for implicit SSL (commonly 465), smtp + STARTTLS on 587.
            $scheme = match ($enc) {
                'ssl' => 'smtps',
                default => $port === 465 ? 'smtps' : 'smtp',
            };

            $base = config('mail.mailers.smtp');

            config([
                'mail.mailers.smtp' => array_merge($base, [
                    'scheme' => $scheme,
                    'host' => $row['mail_host'] ?? $base['host'] ?? '127.0.0.1',
                    'port' => $port > 0 ? $port : 587,
                    'username' => $row['mail_username'] ?? $base['username'],
                    'password' => ($row['mail_password'] ?? '') !== '' ? $row['mail_password'] : ($base['password'] ?? ''),
                ]),
            ]);
        }

        if (! empty($row['mail_from_address'])) {
            config([
                'mail.from.address' => $row['mail_from_address'],
                'mail.from.name' => $row['mail_from_name'] ?: config('mail.from.name'),
            ]);
        }
    }
}
