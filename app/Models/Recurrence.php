<?php

namespace App\Models;

use App\Models\Concerns\IsMasterCatalogRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
    use IsMasterCatalogRecord;

    /**
     * Recurrence names whose interval is a fixed number of days — used only to
     * suggest a starting value for interval_days, never at scheduling time.
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

    /** Names with no fixed day count to suggest (one-time / calendar-based). */
    private const NO_SUGGESTION_KEYWORDS = ['one-time', 'one time', 'monthly', 'quarterly'];

    protected $fillable = [
        'name',
        'interval_days',
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

    /**
     * Resolve the next scheduled date from a given date.
     * Driven entirely by the stored interval_days — never re-derived from the
     * name at scheduling time, so a job's next date never silently depends on
     * whether a name string happens to parse. Returns null when interval_days
     * isn't set (e.g. a one-time recurrence).
     */
    public function resolve(Carbon $from): ?Carbon
    {
        return $this->interval_days !== null
            ? $from->copy()->addDays($this->interval_days)
            : null;
    }

    /**
     * Best-effort day-count suggestion for a recurrence name. Used to seed
     * interval_days for historical rows with no stored value yet; the admin
     * form suggests the same thing client-side and lets the user override it.
     * Returns null when nothing can be inferred — never throws.
     */
    public static function parseIntervalDays(string $name): ?int
    {
        $normalised = strtolower(trim($name));

        if ($normalised === '' || in_array($normalised, self::NO_SUGGESTION_KEYWORDS, true)) {
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

        return null;
    }
}
