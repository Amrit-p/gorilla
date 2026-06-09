<?php

namespace App\Models;

use App\Enums\FollowupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Followup extends Model
{
    protected $fillable = [
        'followable_type',
        'followable_id',
        'created_by',
        'outcome',
        'notes',
        'status',
        'next_followup_at',
    ];

    protected $casts = [
        'next_followup_at' => 'datetime',
        'status' => FollowupStatus::class,
    ];

    public function followable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
