<?php

/**
 * Gorilla CRM permission matrix (single source of truth).
 *
 * Add new permissions here, map them to roles, then use CrmPermissions helpers
 * in policies, routes, and Blade (@can / Gate).
 */
return [
    'guard' => 'web',

    'permissions' => [
        'view_dashboard',
        'manage_leads',
        'manage_customers',
        'manage_jobs',
        'assign_jobs',
        'manage_users',
        'manage_masters',
        'upload_job_images',
        'view_mower_report',
    ],

    'roles' => [
        'office_manager' => [
            'label' => 'Office Manager',
            'description' => 'Full administrative access (Gorilla admin).',
            'permissions' => '*',
        ],
        'sales_manager' => [
            'label' => 'Sales Manager',
            'description' => 'Lead and customer management.',
            'permissions' => [
                'view_dashboard',
                'manage_leads',
                'manage_customers',
                'view_mower_report',
            ],
        ],
        'mower' => [
            'label' => 'Mower',
            'description' => 'Field access: view jobs, mobile updates, upload images.',
            'permissions' => [
                'view_dashboard',
                'manage_jobs',
                'upload_job_images',
                'view_mower_report',
            ],
        ],
    ],

    /** Roles selectable when creating users (Office Manager assigned via RBAC). */
    'assignable_role_keys' => [
        'sales_manager',
        'mower',
    ],

    /** Roles selectable on the RBAC screen. */
    'rbac_assignable_role_keys' => [
        'office_manager',
        'sales_manager',
        'mower',
    ],

    /**
     * Maps legacy Spatie permission names to Gorilla CRM permissions (upgrades).
     */
    'legacy_permissions' => [
        'dashboard.view' => 'view_dashboard',
        'lead.view' => 'manage_leads',
        'lead.create' => 'manage_leads',
        'lead.edit' => 'manage_leads',
        'lead.delete' => 'manage_leads',
        'client.view' => 'manage_customers',
        'client.create' => 'manage_customers',
        'job.view' => 'manage_jobs',
        'job.create' => 'manage_jobs',
        'job.assign' => 'assign_jobs',
        'user.manage' => 'manage_users',
    ],

    /**
     * Maps legacy role display names to Gorilla CRM roles (upgrades).
     */
    'legacy_roles' => [
        'Super Admin' => 'office_manager',
        'Office Manager' => 'office_manager',
        'Sales Manager' => 'sales_manager',
        'Sales Person' => 'sales_manager',
        'Mowers' => 'mower',
        'Employee' => 'mower',
    ],
];
