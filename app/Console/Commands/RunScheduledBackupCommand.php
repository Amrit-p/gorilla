<?php

namespace App\Console\Commands;

use App\Enums\BackupStatus;
use App\Jobs\CreateDatabaseBackupJob;
use App\Models\DatabaseBackup;
use App\Models\Setting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('backup:run')]
#[Description('Run a scheduled database backup if it is due based on the configured schedule.')]
class RunScheduledBackupCommand extends Command
{
    public function handle(): int
    {
        $settings = Setting::query()->pluck('value', 'key');
        $schedule = $settings->get('backup_schedule', 'weekly');

        if (! $this->isDue($schedule)) {
            $this->info("Backup not due yet (schedule: {$schedule}). Skipping.");

            return self::SUCCESS;
        }

        $disk = $settings->get('backup_disk', 'local');

        $backup = DatabaseBackup::query()->create([
            'filename' => 'pending',
            'disk' => $disk,
            'path' => '',
            'status' => BackupStatus::Pending,
            'triggered_by' => null,
        ]);

        CreateDatabaseBackupJob::dispatch($backup->id);

        $this->info("Backup #{$backup->id} queued (schedule: {$schedule}, disk: {$disk}).");

        return self::SUCCESS;
    }

    private function isDue(string $schedule): bool
    {
        $lastCompleted = DatabaseBackup::query()
            ->where('status', BackupStatus::Completed)
            ->latest()
            ->value('created_at');

        if (! $lastCompleted) {
            return true;
        }

        $intervalDays = match ($schedule) {
            'biweekly' => 14,
            'monthly' => 30,
            default => 7, // weekly
        };

        return now()->diffInDays($lastCompleted) >= $intervalDays;
    }
}
