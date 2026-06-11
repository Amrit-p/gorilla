<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionSection extends Model
{
    use HasFactory;

    protected $fillable = ['discussion_id', 'sort_order', 'heading', 'body'];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DiscussionAttachment::class);
    }
}
