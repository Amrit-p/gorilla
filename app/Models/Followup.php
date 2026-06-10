<?php

namespace App\Models;

use App\Enums\FollowupStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Followup extends Model
{
    protected $fillable = [
        'followable_type',
        'followable_id',
        'created_by',
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

    public function followableUrl(): ?string
    {
        return match ($this->followable_type) {
            \App\Models\Job::class => route('admin.jobs.show', $this->followable_id),
            \App\Models\Lead::class => route('admin.leads.show', $this->followable_id),
            \App\Models\Contractor::class => route('admin.contractors.show', $this->followable_id),
            default => null,
        };
    }

    public function followableLabel(): string
    {
        $id = $this->followable_id;
        $followable = $this->followable;

        if ($followable === null) {
            return "#{$id}";
        }

        return match ($this->followable_type) {
            \App\Models\Job::class => $this->buildJobLabel($followable),
            \App\Models\Lead::class => "#{$id}"
                .($followable->client_name ? " – {$followable->client_name}" : '')
                .($followable->email ? " ({$followable->email})" : ''),
            \App\Models\Contractor::class => "#{$id}"
                .($followable->name ? " – {$followable->name}" : '')
                .($followable->email ? " ({$followable->email})" : ''),
            default => "#{$id}",
        };
    }

    private function buildJobLabel(\App\Models\Job $job): string
    {
        $client = $job->relationLoaded('client') ? $job->client : $job->client()->first(['id', 'name']);
        $label = "#{$job->id}";
        if ($client) {
            $label .= " – {$client->name}";
        }
        if ($job->client_address) {
            $label .= " ({$job->client_address})";
        }

        return $label;
    }

}
