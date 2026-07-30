<?php

namespace App\Models;

use App\Traits\HasFollowups;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'zone_id',
    'client_name',
    'email',
    'mobile_number',
    'address',
    'service_types',
    'weed_spray',
    'equipment_type_id',
    'recurrence_id',
    'job_type',
    'charges',
    'payment_mode',
    'payment_status',
    'remarks',
    'property_details',
    'assigned_sales_user_id',
    'status',
    'is_locked',
    'queued_for_scheduling',
    'latitude',
    'longitude',
    'lead_date',
    'lead_time',
    'converted_at',
])]
class Lead extends Model
{
    use HasFollowups;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'service_types' => 'array',
            'is_locked' => 'boolean',
            'queued_for_scheduling' => 'boolean',
            'converted_at' => 'datetime',
            'charges' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'lead_date' => 'date',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function assignedSalesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_sales_user_id');
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function recurrence(): BelongsTo
    {
        return $this->belongsTo(Recurrence::class);
    }

    public function job(): HasOne
    {
        return $this->hasOne(Job::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function leadNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }
}
