<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MowerReportPayout extends Model
{
    protected $fillable = [
        'user_id',
        'period_key',
        'period_start',
        'period_end',
        'amount',
        'set_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public static function periodKey(?string $startDate, ?string $endDate): string
    {
        return ($startDate ?: 'all').'_'.($endDate ?: 'all');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}
