<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id',
    'amount',
    'bonus',
    'comment',
    'created_by',
])]
class MowerPayout extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bonus' => 'decimal:2',
        ];
    }

    /** The mower being paid. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The completed jobs this payout settles. */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'mower_payout_job', 'mower_payout_id', 'job_id')
            ->withTimestamps();
    }

    /** Payout plus bonus, i.e. what the mower actually receives. */
    public function totalAmount(): float
    {
        return round((float) $this->amount + (float) $this->bonus, 2);
    }
}
