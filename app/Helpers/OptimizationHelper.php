<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * Small helpers for Module 14 (Optimization): forget cached values when domain data changes.
 * Keeping this in one place avoids scattering cache key strings across services.
 */
class OptimizationHelper
{
    public static function forgetDashboardStats(): void
    {
        $key = config('mowing.cache_keys.dashboard_stats');
        Cache::forget($key);
        Cache::forget($key.'.lead_status');
        self::bumpDashboardAnalyticsGeneration();
    }

    public static function bumpDashboardAnalyticsGeneration(): void
    {
        $generationKey = 'mowing.dashboard.analytics_generation';
        Cache::forever($generationKey, (int) Cache::get($generationKey, 1) + 1);
    }

    public static function dashboardAnalyticsGeneration(): int
    {
        return (int) Cache::get('mowing.dashboard.analytics_generation', 1);
    }

    public static function forgetSettings(): void
    {
        Cache::forget(config('mowing.cache_keys.settings_flat'));
    }

    /**
     * Bump a global map-cache generation so every map payload key becomes stale instantly.
     * Avoids Redis tag flush and works with file/array drivers.
     */
    public static function bumpMapCacheGeneration(): void
    {
        $key = 'mowing.map.cache_generation';
        Cache::forever($key, (int) Cache::get($key, 1) + 1);
    }

    /**
     * Build the cache key used by MapRoutingService for a given date or "all".
     */
    public static function mapJobsCacheKey(?string $scheduledDate = null): string
    {
        $suffix = $scheduledDate ? 'date:'.$scheduledDate : 'all';
        $generation = (int) Cache::get('mowing.map.cache_generation', 1);

        return config('mowing.cache_keys.map_jobs_prefix').':'.$suffix.':g'.$generation;
    }

    public static function notificationUnreadKey(int $userId): string
    {
        return config('mowing.cache_keys.notification_unread_prefix').':'.$userId;
    }

    public static function forgetNotificationUnreadCount(?int $userId = null): void
    {
        if ($userId) {
            Cache::forget(self::notificationUnreadKey($userId));

            return;
        }

        // Fallback: no user id — nothing to clear specifically.
    }
}
