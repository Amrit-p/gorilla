<?php

namespace App\Support;

/**
 * Centralized Gorilla CRM constants (cache keys, scopes, pagination).
 */
final class CrmConstants
{
    public const DEFAULT_PAGINATION = 15;

    /**
     * Page-size sentinel meaning "no page limit for the user".
     *
     * It never turns into an unbounded query: the list is still fetched a
     * chunk at a time, the front-end just requests the next chunk on scroll.
     */
    public const PAGINATION_ALL = 'all';

    public const INFINITE_SCROLL_CHUNK = 100;

    public const JOB_LIST_SCOPE_TODAY = 'today';

    public const JOB_LIST_SCOPE_UPCOMING = 'upcoming';

    public const JOB_LIST_SCOPE_DONE = 'done';

    public const JOB_LIST_SCOPE_HOLD = 'hold';

    public const JOB_LIST_SCOPE_COMPLETED_UNVERIFIED = 'completed_unverified';

    public const JOB_LIST_SCOPE_DELETED = 'deleted';

    public const MOWER_SCOPE_TODAY = 'today';

    public const MOWER_SCOPE_UPCOMING = 'upcoming';

    public const MOWER_SCOPE_COMPLETED = 'completed';

    public const MOWER_SCOPE_HOLD = 'hold';

    public const MOWER_SCOPE_PENDING = 'pending';

    public const MOWER_SCOPE_TODAY_SPECIAL = 'today-special';

    public const MOWER_SCOPE_STARTED = 'started';

    /**
     * @return array<int, string>
     */
    public static function jobListScopes(): array
    {
        return [
            self::JOB_LIST_SCOPE_TODAY,
            self::JOB_LIST_SCOPE_UPCOMING,
            self::JOB_LIST_SCOPE_DONE,
            self::JOB_LIST_SCOPE_HOLD,
        ];
    }

    public static function defaultPagination(): int
    {
        return (int) config('mowing.default_pagination', self::DEFAULT_PAGINATION);
    }

    /**
     * Selectable page sizes for CRM list tables, smallest first.
     *
     * @return array<int, int>
     */
    public static function paginationOptions(): array
    {
        $options = array_map('intval', (array) config('mowing.pagination_options', [self::DEFAULT_PAGINATION]));
        $options = array_values(array_unique(array_filter($options, fn (int $size): bool => $size > 0)));

        sort($options);

        return $options === [] ? [self::DEFAULT_PAGINATION] : $options;
    }

    public static function infiniteScrollChunk(): int
    {
        $chunk = (int) config('mowing.infinite_scroll_chunk', self::INFINITE_SCROLL_CHUNK);

        return $chunk > 0 ? $chunk : self::INFINITE_SCROLL_CHUNK;
    }

    public static function dashboardStatsCacheKey(): string
    {
        return (string) config('mowing.cache_keys.dashboard_stats', 'mowing.cache.dashboard_stats_v1');
    }

    public static function dashboardStatsTtl(): int
    {
        return (int) config('mowing.cache.ttl.dashboard_stats_seconds', 90);
    }
}
