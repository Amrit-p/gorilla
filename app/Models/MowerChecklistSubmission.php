<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MowerChecklistSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'checklist_point_id',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checklistPoint(): BelongsTo
    {
        return $this->belongsTo(ChecklistPoint::class);
    }
}
