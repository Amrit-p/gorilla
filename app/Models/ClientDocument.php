<?php

namespace App\Models;

use Database\Factories\ClientDocumentFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientDocument extends Model
{
    /** @use HasFactory<ClientDocumentFactory> */
    use HasFactory;

    /**
     * Filesystem disk used to store client documents.
     *
     * Change this single constant to relocate every client document to
     * another disk (e.g. 's3', 'GCS') without touching the rest of the codebase.
     */
    public const DISK = 'public';

    protected $fillable = [
        'client_id',
        'original_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Storage disk instance backing this document.
     */
    public static function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }
}
