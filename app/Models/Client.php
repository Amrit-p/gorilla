<?php

namespace App\Models;

use App\Support\CustomerUniqueIdGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'customer_unique_id',
    'lead_id',
    'zone_id',
    'accounting_level_id',
    'job_level_id',
    'equipment_type_id',
    'name',
    'email',
    'phone',
    'address',
    'service_types',
    'weed_spray',
    'recurrence_id',
    'job_type',
    'safety_concerns', // This field is not being stored because it is not currently being used in the application.
    'safety_other', // This field is not being stored because it is not currently being used in the application.
    'charges',
    'estimated_time',
    'payment_mode',
    'remarks_type',
    'payment_status',
    'payment_status_reason',
    'latitude',
    'longitude',
    'property_details',
    'additional_site_instructions',
    'pet_warning', // This field is not being stored because it is not currently being used in the application.
    'special_remarks',
    'notes',
    'client_type',
    'parking_status', // This field is not being stored because it is not currently being used in the application.
    'customer_type',
    'created_by',
])]
class Client extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Client $client): void {
            if ($client->customer_unique_id === null) {
                $client->customer_unique_id = CustomerUniqueIdGenerator::next();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'service_types' => 'array',
            'safety_concerns' => 'array',
            'charges' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'customer_unique_id' => 'integer',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function accountingLevel(): BelongsTo
    {
        return $this->belongsTo(AccountingLevel::class);
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    protected function getTotalChargesAttribute(): float
    {
        return $this->jobs()->sum('charges');
    }

    public function recurrence(): BelongsTo
    {
        return $this->belongsTo(Recurrence::class);
    }
}
