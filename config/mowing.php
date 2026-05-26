<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mowing CRM Settings
    |--------------------------------------------------------------------------
    |
    | Central place for project-specific constants.
    | Keeping these values in config files makes future modules easier to scale.
    |
    */
    'default_pagination' => 15,

    /** First auto-generated Gorilla CRM user ID (user_unique_id). */
    'user_unique_id_start' => 1001,

    /** First auto-generated Gorilla CRM customer ID (customer_unique_id). */
    'customer_unique_id_start' => 2001,

    /*
    |--------------------------------------------------------------------------
    | Website defaults (overridden by Admin → Website Settings when saved)
    |--------------------------------------------------------------------------
    */
    'website_defaults' => [
        'site_name' => env('APP_NAME', 'Mowing CRM'),
        'site_tagline' => 'Operations hub',
        'site_logo_initial' => 'M',
        'company_name' => env('APP_NAME', 'Mowing CRM'),
        'company_phone' => '',
        'company_email' => '',
        'seo_meta_title' => '',
        'seo_meta_description' => '',
        'seo_meta_keywords' => '',
        'seo_og_title' => '',
        'seo_og_description' => '',
        'seo_robots_noindex' => '0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Service types (Leads, Clients, Jobs — multi-select on all intake forms)
    |--------------------------------------------------------------------------
    */
    'service_types' => [
        'Mulching',
        'Side shoot',
        'Cut and leave',
        'Cut and Away',
    ],

    // Supported user roles for upcoming RBAC module.
    'roles' => [
        'super_admin',
        'sales',
        'manager',
        'employee',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance & caching (Module: Optimization)
    |--------------------------------------------------------------------------
    |
    | Short TTLs keep the UI fresh while cutting repeated aggregate queries.
    | Invalidate dashboard stats when leads, clients, or jobs change (see observers).
    |
    */
    'cache' => [
        'ttl' => [
            'dashboard_stats_seconds' => 90,
            'settings_seconds' => 600,
            'map_jobs_seconds' => 30,
            'notification_unread_seconds' => 20,
            'master_catalog_seconds' => 300,
        ],
    ],

    // Stable cache key names — used by helpers + observers for invalidation.
    'cache_keys' => [
        'dashboard_stats' => 'mowing.cache.dashboard_stats_v1',
        'settings_flat' => 'mowing.cache.settings_flat_v1',
        'map_jobs_prefix' => 'mowing.cache.map_jobs_v1',
        'notification_unread_prefix' => 'mowing.cache.notification_unread_v1',
    ],
];
