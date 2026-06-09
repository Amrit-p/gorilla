<?php

namespace App\Traits;

use App\Models\Followup;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasFollowups
{
    public function followups(): MorphMany
    {
        return $this->morphMany(Followup::class, 'followable')->orderByDesc('created_at');
    }

    public function latestFollowup(): MorphOne
    {
        return $this->morphOne(Followup::class, 'followable')->latestOfMany();
    }
}
