<?php

namespace App\Models;

use App\Models\Concerns\IsMasterCatalogRecord;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
    use IsMasterCatalogRecord;

    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Resolve the next scheduled date from a given date based on this recurrence's name.
     * Returns null for one-time recurrences or unrecognised patterns.
     */
    public function resolve(Carbon $from): ?Carbon
    {
        $name = strtolower(trim($this->name));

        if (str_contains($name, 'one-time') || str_contains($name, 'one time')) {
            return null;
        }

        $namedIntervals = [
            'daily' => fn (Carbon $d): Carbon => $d->addDay(),
            'weekly' => fn (Carbon $d): Carbon => $d->addWeek(),
            'bi-weekly' => fn (Carbon $d): Carbon => $d->addWeeks(2),
            'biweekly' => fn (Carbon $d): Carbon => $d->addWeeks(2),
            'monthly' => fn (Carbon $d): Carbon => $d->addMonth(),
            'quarterly' => fn (Carbon $d): Carbon => $d->addMonths(3),
        ];

        if (isset($namedIntervals[$name])) {
            return $namedIntervals[$name]($from->copy());
        }

        // Normalise patterns like "2 weekly" → "2 weeks", "1 week" → "1 weeks"
        $normalised = preg_replace('/(\d+)\s+week(?:ly)?s?/i', '$1 weeks', $name) ?? $name;
        $normalised = preg_replace('/(\d+)\s+month(?:ly)?s?/i', '$1 months', $normalised) ?? $normalised;
        $normalised = preg_replace('/(\d+)\s+days?/i', '$1 days', $normalised) ?? $normalised;

        try {
            $interval = CarbonInterval::fromString($normalised);

            if ($interval->totalSeconds > 0) {
                return $from->copy()->add($interval);
            }
        } catch (\Throwable) {
        }

        return null;
    }
}
