<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BackupStatus;
use App\Http\Controllers\Controller;
use App\Jobs\CreateDatabaseBackupJob;
use App\Models\DatabaseBackup;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function list(): JsonResponse
    {
        $backups = DatabaseBackup::query()
            ->with('actor:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (DatabaseBackup $b) => [
                'id' => $b->id,
                'filename' => $b->filename,
                'disk' => $b->disk,
                'size' => $b->formattedSize(),
                'status' => $b->status->value,
                'status_label' => $b->status->label(),
                'status_badge' => $b->status->badgeClass(),
                'error_message' => $b->error_message,
                'actor' => $b->actor?->name ?? 'Scheduled',
                'created_at' => $b->created_at->format('d M Y H:i'),
                'download_url' => $b->status === BackupStatus::Completed
                    ? route('admin.settings.backups.download', $b)
                    : null,
                'delete_url' => route('admin.settings.backups.destroy', $b),
            ]);

        return response()->json(['backups' => $backups]);
    }

    public function trigger(Request $request): JsonResponse
    {
        $disk = Setting::query()->where('key', 'backup_disk')->value('value') ?? 'local';

        $backup = DatabaseBackup::query()->create([
            'filename' => 'pending',
            'disk' => $disk,
            'path' => '',
            'status' => BackupStatus::Pending,
            'triggered_by' => $request->user()->id,
        ]);

        CreateDatabaseBackupJob::dispatch($backup->id);

        return response()->json(['message' => 'Backup queued. It will appear in the list when complete.']);
    }

    public function download(DatabaseBackup $databaseBackup): StreamedResponse|RedirectResponse
    {
        abort_unless($databaseBackup->status === BackupStatus::Completed, 404);

        if ($databaseBackup->disk === 'gcs') {
            $settings = Setting::query()->pluck('value', 'key')->toArray();
            $disk = Storage::build([
                'driver' => 'gcs',
                'project_id' => $settings['backup_gcs_project'] ?? '',
                'key_file_path' => ($settings['backup_gcs_key_file'] ?? '') ?: null,
                'bucket' => $settings['backup_gcs_bucket'] ?? '',
                'path_prefix' => '',
            ]);

            return redirect($disk->temporaryUrl($databaseBackup->path, now()->addMinutes(10)));
        }

        $localDisk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/private/backups'),
        ]);

        abort_unless($localDisk->exists($databaseBackup->filename), 404);

        return $localDisk->download($databaseBackup->filename, $databaseBackup->filename);
    }

    public function destroy(DatabaseBackup $databaseBackup): JsonResponse
    {
        if ($databaseBackup->status === BackupStatus::Completed) {
            $this->deleteFromStorage($databaseBackup);
        }

        $databaseBackup->delete();

        return response()->json(['message' => 'Backup deleted.']);
    }

    private function deleteFromStorage(DatabaseBackup $backup): void
    {
        try {
            if ($backup->disk === 'gcs') {
                $settings = Setting::query()->pluck('value', 'key')->toArray();
                $disk = Storage::build([
                    'driver' => 'gcs',
                    'project_id' => $settings['backup_gcs_project'] ?? '',
                    'key_file_path' => ($settings['backup_gcs_key_file'] ?? '') ?: null,
                    'bucket' => $settings['backup_gcs_bucket'] ?? '',
                    'path_prefix' => '',
                ]);
                $disk->delete($backup->path);
            } else {
                $localDisk = Storage::build([
                    'driver' => 'local',
                    'root' => storage_path('app/private/backups'),
                ]);
                $localDisk->delete($backup->filename);
            }
        } catch (\Throwable) {
            // Silently continue — record is still deleted from the database
        }
    }
}
