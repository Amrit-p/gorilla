<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contract_id',
        'status',
        'changed_by',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'changed_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
