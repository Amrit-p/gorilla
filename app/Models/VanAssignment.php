<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VanAssignment extends Model
{
    protected $fillable = [
        'van_id',
        'user_id',
        'date',
    ];

    /**
     * @return BelongsTo<Van, $this>
     */
    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
