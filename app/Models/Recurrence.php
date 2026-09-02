<?php

namespace App\Models;

use App\Models\Concerns\IsMasterCatalogRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Recurrence extends Model
{
    use IsMasterCatalogRecord;

    /**
     * Recurrence names whose interval is a fixed number of days — safe to
     * pre-compute and store, unlike calendar-based months/quarters.
     *
     * @var array<string, int>
     */
    private const FIXED_DAY_KEYWORDS = [
        'daily' => 1,
        'weekly' => 7,
        'bi-weekly' => 14,
        'biweekly' => 14,
        'fortnightly' => 14,
    ];

    /** Recurrence names resolved via calendar arithmetic (variable month length). */
    private const CALENDAR_KEYWORDS = ['monthly', 'quarterly'];

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
            'interval_days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // interval_days is never user-supplied — it's always derived from name,
        // so a bad/unparseable pattern is caught here instead of failing later
        // when a job completes.
        static::saving(function (Recurrence $recurrence): void {
            $recurrence->interval_days = self::parseIntervalDays((string) $recurrence->name);
        });
    }

    /**
     * Resolve the next scheduled date from a given date based on this recurrence.
     * Returns null for one-time recurrences or unrecognised patterns.
     */
    public function resolve(Carbon $from): ?Carbon
    {
        $name = strtolower(trim($this->name));

        if ($name === 'monthly') {
            return $from->copy()->addMonth();
        }

        if ($name === 'quarterly') {
            return $from->copy()->addMonths(3);
        }

        return $this->interval_days !== null
            ? $from->copy()->addDays($this->interval_days)
            : null;
    }

    /**
     * Derive the fixed day interval for a recurrence name.
     * Returns null for one-time and calendar-based (monthly/quarterly) names,
     * which don't need a stored day count.
     *
     * @throws InvalidArgumentException when the name can't be parsed at all.
     */
    public static function parseIntervalDays(string $name): ?int
    {
        $normalised = strtolower(trim($name));

        if ($normalised === '') {
            throw new InvalidArgumentException('Recurrence name is required.');
        }

        if (str_contains($normalised, 'one-time') || str_contains($normalised, 'one time')) {
            return null;
        }

        if (in_array($normalised, self::CALENDAR_KEYWORDS, true)) {
            return null;
        }

        if (isset(self::FIXED_DAY_KEYWORDS[$normalised])) {
            return self::FIXED_DAY_KEYWORDS[$normalised];
        }

        // Numeric multiplier patterns, with or without a separator:
        // "3 weeks", "3-weekly", "3_weekly", "3weeks", "10 days" …
        if (preg_match('/^(\d+)\s*[-_]?\s*(days?|weeks?|weekly)$/', $normalised, $matches)) {
            $count = max(1, (int) $matches[1]);

            return str_starts_with($matches[2], 'week') ? $count * 7 : $count;
        }

        throw new InvalidArgumentException("Could not parse the recurrence pattern \"{$name}\".");
    }
}
