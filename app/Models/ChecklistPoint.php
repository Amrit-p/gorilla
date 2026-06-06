<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistPoint extends Model
{
    protected $fillable = [
        'checklist_id',
        'heading',
        'text',
        'sort_order',
        'is_archived',
        'archived_from_point_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order'  => 'integer',
            'is_archived' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('not_archived', fn (Builder $q) => $q->where('is_archived', false));
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(ChecklistPoint::class, 'archived_from_point_id')
            ->withoutGlobalScope('not_archived');
    }
}
