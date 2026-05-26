<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'action', 'description', 'context', 'ip_address', 'user_agent'])]
class ActivityLog extends Model
{
    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function user(): BelongsToRelation
    {
        return $this->belongsTo(User::class);
    }
}
