<?php

namespace App\Models;

use App\Enums\BackupStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['filename', 'disk', 'path', 'size_bytes', 'status', 'error_message', 'triggered_by'])]
class DatabaseBackup extends Model
{
    protected $casts = [
        'status' => BackupStatus::class,
        'size_bytes' => 'integer',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function formattedSize(): string
    {
        if (! $this->size_bytes) {
            return '—';
        }

        $bytes = (float) $this->size_bytes;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}
