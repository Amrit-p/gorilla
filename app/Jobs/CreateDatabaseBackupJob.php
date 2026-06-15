<?php

namespace App\Jobs;

use App\Enums\BackupStatus;
use App\Models\DatabaseBackup;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class CreateDatabaseBackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(public int $backupId) {}

    public function handle(): void
    {
        $backup = DatabaseBackup::query()->find($this->backupId);
        if (! $backup) {
            return;
        }

        $backup->update(['status' => BackupStatus::Running]);

        $sqlPath = null;
        $zipPath = null;

        try {
            $settings = Setting::query()->pluck('value', 'key')->toArray();
            $timestamp = now()->format('Y-m-d_H-i-s');
            $sqlFilename = "backup_{$timestamp}.sql";
            $zipFilename = "backup_{$timestamp}.zip";
            $tmpDir = storage_path('app/tmp');

            if (! is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $sqlPath = "{$tmpDir}/{$sqlFilename}";
            $zipPath = "{$tmpDir}/{$zipFilename}";

            $this->dumpDatabase($sqlPath, $settings);

            $this->createZip($zipPath, $sqlPath, $sqlFilename);

            $storagePath = 'backups/'.$zipFilename;
            $disk = $this->resolveDisk($backup->disk, $settings);
            $disk->putFileAs('backups', new File($zipPath), $zipFilename);

            $size = filesize($zipPath);

            @unlink($sqlPath);
            @unlink($zipPath);

            $backup->update([
                'status' => BackupStatus::Completed,
                'filename' => $zipFilename,
                'path' => $storagePath,
                'size_bytes' => $size,
            ]);
        } catch (Throwable $e) {
            if ($sqlPath) {
                @unlink($sqlPath);
            }
            if ($zipPath) {
                @unlink($zipPath);
            }

            $backup->update([
                'status' => BackupStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, string>  $settings
     */
    private function dumpDatabase(string $outputPath, array $settings): void
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");

        if ($driver === 'sqlite') {
            $dbFile = $connection['database'] ?? database_path('database.sqlite');
            if (! copy($dbFile, $outputPath)) {
                throw new RuntimeException("Failed to copy SQLite database from {$dbFile}");
            }

            return;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $binary = $settings['backup_mysqldump_path'] ?? 'mysqldump';

            $process = new Process([
                $binary,
                '--host='.$connection['host'],
                '--port='.($connection['port'] ?? 3306),
                '--user='.$connection['username'],
                '--password='.$connection['password'],
                '--single-transaction',
                '--routines',
                '--triggers',
                '--skip-lock-tables',
                $connection['database'],
            ]);
            $process->setTimeout(300);
            $process->mustRun();

            file_put_contents($outputPath, $process->getOutput());

            return;
        }

        throw new RuntimeException("Unsupported database driver: {$driver}");
    }

    private function createZip(string $zipPath, string $sqlPath, string $sqlFilename): void
    {
        $zip = new ZipArchive;
        $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new RuntimeException("Cannot create zip archive (error code {$result}): {$zipPath}");
        }

        $zip->addFile($sqlPath, $sqlFilename);
        $zip->close();
    }

    /**
     * @param  array<string, string>  $settings
     */
    private function resolveDisk(string $diskName, array $settings): FilesystemAdapter
    {
        if ($diskName === 'gcs') {
            return Storage::build([
                'driver' => 'gcs',
                'project_id' => $settings['backup_gcs_project'] ?? '',
                'key_file_path' => ($settings['backup_gcs_key_file'] ?? '') ?: null,
                'bucket' => $settings['backup_gcs_bucket'] ?? '',
                'path_prefix' => '',
            ]);
        }

        return Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/private/backups'),
        ]);
    }
}
