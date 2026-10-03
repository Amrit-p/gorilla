<?php

namespace App\Models;

use App\Observers\JobObserver;
use App\Traits\HasFollowups;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'customer_name',
    'email',
    'phone',
    'weed_spray',
    'job_type',
    'property_details',
    'notes',
    'accounting_level_id',
    'client_rating_id',
    'lead_id',
    'zone_id',
    'equipment_type_id',
    'van_number',
    'job_level_id',
    'contract_id',
    'recurrence_id',
    'client_address',
    'latitude',
    'longitude',
    'scheduled_date',
    'scheduled_time',
    'estimated_duration_minutes',
    'consumed_time_minutes',
    'required_services',
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
    'payment_pending_amount',
    'received_amount',
    'charges',
    'incentive_percentage',
    'office_remarks',
    'mower_remarks',
    'internal_notes',
    'created_by',
    'numeric_priority',
    'verified_at',
    'verified_by',
    'verified_date',
    'verified_amount',
    'verification_comment',
    'second_payment',
    'rescheduled_from_job_id',
])]
#[ObservedBy([JobObserver::class])]
class Job extends Model
{
    use HasFollowups;
    use SoftDeletes;

    protected $table = 'service_jobs';

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'required_services' => 'array',
            'attached_images' => 'array',
            'before_images' => 'array',
            'after_images' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'numeric_priority' => 'integer',
            'charges' => 'decimal:2',
            'payment_pending_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'incentive_percentage' => 'decimal:2',
            'verified_at' => 'datetime',
            'verified_date' => 'date:Y-m-d',
            'verified_amount' => 'decimal:2',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function accountingLevel(): BelongsTo
    {
        return $this->belongsTo(AccountingLevel::class);
    }

    public function clientRating(): BelongsTo
    {
        return $this->belongsTo(ClientRating::class);
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }

    /**
     * Contact name on the job, with sensible fallbacks when name was never set.
     */
    public function customerDisplayName(): string
    {
        $name = trim((string) ($this->customer_name ?: ''));
        if ($name !== '') {
            return $name;
        }

        $address = trim((string) ($this->client_address ?: ''));
        if ($address !== '') {
            return $address;
        }

        return 'N/A';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function doneByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by_user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function assignedEmployees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_user_assignments')
            ->withPivot(['assignment_date', 'assignment_status', 'incentive_percentage'])
            ->withTimestamps();
    }

    public function recurrence(): BelongsTo
    {
        return $this->belongsTo(Recurrence::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /** The next recurring occurrence created when this job was completed, if any. */
    public function nextOccurrence(): HasOne
    {
        return $this->hasOne(self::class, 'rescheduled_from_job_id');
    }

    /** Payouts that settle this job. The unique pivot key allows at most one. */
    public function payouts(): BelongsToMany
    {
        return $this->belongsToMany(MowerPayout::class, 'mower_payout_job', 'job_id', 'mower_payout_id')
            ->withTimestamps();
    }

    /**
     * Whether the mower has been paid out for this job. Deliberately not named
     * "paid": on this model `payment_status` already means the customer paying
     * us, which is what the dashboard's "Unpaid" counts refer to.
     */
    public function hasMowerPayout(): bool
    {
        return $this->payouts()->exists();
    }

    /** @param  Builder<self>  $query */
    public function scopeWithMowerPayout(Builder $query): void
    {
        $query->whereHas('payouts');
    }

    /** @param  Builder<self>  $query */
    public function scopeWithoutMowerPayout(Builder $query): void
    {
        $query->whereDoesntHave('payouts');
    }
}
