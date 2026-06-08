<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DiscussionAttachment extends Model
{
    protected $fillable = ['discussion_section_id', 'original_name', 'file_path', 'file_type'];

    protected $appends = ['url'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(DiscussionSection::class, 'discussion_section_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
