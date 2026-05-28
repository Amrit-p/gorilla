<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id',
    'lead_id',
    'zone_id',
    'equipment_type_id',
    'client_address',
    'latitude',
    'longitude',
    'scheduled_date',
    'scheduled_time',
    'estimated_duration_minutes',
    'consumed_time_minutes',
    'required_services',
    'is_recurring',
    'recurrence_pattern',
    'route_sequence',
    'priority',
    'status',
    'site_instructions',
    'parking_status',
    'customer_type',
    'pet_warning',
    'attached_images',
    'before_images',
    'after_images',
    'done_by_user_id',
    'payment_mode',
    'payment_status',
    'payment_pending_reason',
    'special_remarks',
    'internal_notes',
    'created_by',
])]
class Job extends Model
{
    use SoftDeletes;

    protected $table = 'service_jobs';

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'scheduled_time' => 'datetime:H:i',
            'is_recurring' => 'boolean',
            'required_services' => 'array',
            'attached_images' => 'array',
            'before_images' => 'array',
            'after_images' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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

    public function doneByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by_user_id');
    }

    public function assignedEmployees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_user_assignments')
            ->withPivot(['assignment_date', 'assignment_status'])
            ->withTimestamps();
    }
}
