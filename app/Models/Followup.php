<?php

namespace App\Models;

use App\Enums\FollowupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Followup extends Model
{
    /**
     * Filter/select value used for follow-ups that have no polymorphic target.
     */
    public const GENERAL_TYPE = 'general';

    public const GENERAL_LABEL = 'General';

    protected $fillable = [
        'followable_type',
        'followable_id',
        'title',
        'created_by',
        'assigned_to',
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * A general follow-up is a standalone reminder with a title instead of a linked record.
     */
    public function isGeneral(): bool
    {
        return $this->followable_type === null;
    }

    /**
     * Type name shown in lists and filters: "General", "Job", "Lead", …
     */
    public function typeLabel(): string
    {
        return $this->isGeneral()
            ? self::GENERAL_LABEL
            : class_basename($this->followable_type);
    }

    /**
     * Short heading for the follow-up: the title for general ones, "Job #12" otherwise.
     */
    public function subjectLabel(): string
    {
        if ($this->isGeneral()) {
            return $this->title ?: 'General follow-up';
        }

        return class_basename($this->followable_type)." #{$this->followable_id}";
    }

    public function followableUrl(): ?string
    {
        return match ($this->followable_type) {
            Job::class => route('admin.jobs.show', $this->followable_id),
            Lead::class => route('admin.leads.show', $this->followable_id),
            Contractor::class => route('admin.contractors.show', $this->followable_id),
            default => null,
        };
    }

    public function followableLabel(): string
    {
        if ($this->isGeneral()) {
            return $this->title ?: 'General follow-up';
        }

        $id = $this->followable_id;
        $followable = $this->followable;

        if ($followable === null) {
            return "#{$id}";
        }

        return match ($this->followable_type) {
            Job::class => $this->buildJobLabel($followable),
            Lead::class => "#{$id}"
                .($followable->client_name ? " – {$followable->client_name}" : '')
                .($followable->email ? " ({$followable->email})" : ''),
            Contractor::class => "#{$id}"
                .($followable->name ? " – {$followable->name}" : '')
                .($followable->email ? " ({$followable->email})" : ''),
            default => "#{$id}",
        };
    }

    private function buildJobLabel(Job $job): string
    {
        $label = "#{$job->id} – ".$job->customerDisplayName();
        if ($job->client_address) {
            $label .= " ({$job->client_address})";
        }

        return $label;
    }
}
