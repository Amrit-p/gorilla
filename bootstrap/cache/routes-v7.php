<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/_boost/browser-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'boost.browser-logs',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XyKjE8qiYO88i24m',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/clear-cache' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::aO0pnxZ5PnPspJYn',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'home',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'login.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/forgot-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.request',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'password.email',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reset-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/daily-jobs-table' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.daily-jobs-table',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/daily-leads-table' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.daily-leads-table',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/three-week-grid' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.three-week-grid',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/preferences' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.preferences.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/analytics/charts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.analytics.charts',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/mower-performance-table' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.mower-performance-table',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/dashboard/hold-jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'dashboard.hold-jobs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'profile.edit',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'profile.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/profile/password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'profile.password.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/unread' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.unread',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/notifications/read' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'notifications.read',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/rbac' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.rbac.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/activity-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.activity-logs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/backups' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.backups.list',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/settings/backups/trigger' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.backups.trigger',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-bonuses' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-bonuses/export/excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.export.excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-bonuses/export/pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.export.pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-bonuses/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-receipts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-receipts.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-calculator' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-calculator.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-calculator.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-calculator/months' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-calculator.months',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/checklists' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.checklists.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.checklists.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/service-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.service-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.service-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/equipment-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.equipment-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.equipment-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/safety-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.safety-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.safety-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/recurrences' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.recurrences.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.recurrences.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/zones' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.zones.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.zones.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/accounting-levels' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.accounting-levels.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.accounting-levels.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/job-levels' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.job-levels.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.job-levels.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/masters/client-ratings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.client-ratings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.client-ratings.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/contractors' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/followups/form-options' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.form-options',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/followups/for-followable' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.for-followable',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/followups/search-followable' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.search-followable',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/followups' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/followups/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/discussions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/discussions/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads/converted' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.converted',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads/export/excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.export.excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads/export/pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.export.pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads/import/sample' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.import.sample',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leads/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.import',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/import/sample' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.import.sample',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/import' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.import',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/export/excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.export.excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/export/pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.export.pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/reschedule' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.reschedule',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/hold' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.hold',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/mower-suggestions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.mower-suggestions',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/mower-workloads' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.mower-workloads',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/client-remarks' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.client-remarks',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/client-history' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.client-history',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/active-contracts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.active-contracts',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/assign' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.assign',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/contract' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.contract',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/status' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.status.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/verify' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.verify',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/schedule' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.schedule',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/restore' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.restore',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/bulk/force' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.force-destroy',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maps/optimize' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maps.optimize',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maps/assign-nearest' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maps.assign-nearest',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/export/excel' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.export.excel',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/jobs/export/pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.export.pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maps' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maps.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/maps/jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.maps.jobs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/employee/mobile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'employee.mobile.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
            'POST' => 2,
            'PUT' => 3,
            'PATCH' => 4,
            'DELETE' => 5,
            'OPTIONS' => 6,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/checklist' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.checklist.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'mower.checklist.submit',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/map' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.map',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.notifications.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/discussions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.discussions.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/clients/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.clients.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/clients' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.clients.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/mower/export-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.export-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/mower' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.mower.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/mower/report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.mower.report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/mower/jobs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.mower.jobs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/mower/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.mower.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/mower/export-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.mower.export-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/checklist' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.checklist.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/checklist/report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.checklist.report',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/checklist/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.checklist.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/reports/checklist/export-pdf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'reports.checklist.export-pdf',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/reset\\-password/([^/]++)(*:32)|/admin/(?|rbac/users/([^/]++)/role(*:73)|users/([^/]++)(?|/status(*:104)|(*:112))|s(?|ettings/backups/([^/]++)(?|/download(*:161)|(*:169))|alary\\-receipts/([^/]++)(?|(*:205)|/export/pdf(*:224)|(*:232)))|employee\\-bonuses/([^/]++)(?|/edit(*:276)|(*:284))|masters/(?|c(?|hecklists/([^/]++)(?|(*:329))|lient\\-ratings/([^/]++)(?|(*:364)|/status(*:379)|(*:387)))|s(?|ervice\\-types/([^/]++)(?|(*:426)|/status(*:441)|(*:449))|afety\\-types/([^/]++)(?|(*:482)|/status(*:497)|(*:505)))|equipment\\-types/([^/]++)(?|(*:543)|/status(*:558)|(*:566))|recurrences/([^/]++)(?|(*:598)|/status(*:613)|(*:621))|zones/([^/]++)(?|(*:647)|/status(*:662)|(*:670))|accounting\\-levels/([^/]++)(?|(*:709)|/status(*:724)|(*:732))|job\\-levels/([^/]++)(?|(*:764)|/status(*:779)|(*:787)))|c(?|ontractors/([^/]++)(?|/(?|detail(*:833)|jobs(*:845)|contracts(?|(*:865)|/([^/]++)(?|(*:885)|/(?|status(*:903)|documents(?|(*:923)|/([^/]++)(?|/download(*:952)|(*:960)))))))|(*:974))|lients/([^/]++)(?|/(?|edit(*:1009)|jobs(*:1022)|documents/([^/]++)/download(*:1058)|restore(*:1074))|(*:1084)))|followups/([^/]++)(?|(*:1116)|/edit(*:1130)|(*:1139))|discussions/([^/]++)(?|(*:1172)|/e(?|dit(*:1189)|xport\\-pdf(*:1208))|(*:1218))|leads/([^/]++)(?|/(?|edit(*:1253)|status(*:1268)|notes(*:1282))|(*:1292))|jobs/(?|([^/]++)(?|/(?|edit(*:1329)|remarks(*:1345))|(*:1355))|bulk(*:1369)|reorder(*:1385)|([^/]++)(?|/images(*:1412)|(*:1421))))|/mower/(?|discussions/([^/]++)(*:1463)|jobs/([^/]++)(?|(*:1488)|/(?|status(*:1507)|payment(*:1523)|consumed\\-time(*:1546)|images/(?|before(?|(*:1574)|/([^/]++)(*:1592))|after(?|(*:1610)|/([^/]++)(*:1628)))|remark(*:1645))))|/storage/(.*)(?|(*:1673)))/?$}sDu',
    ),
    3 => 
    array (
      32 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.reset',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      73 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.rbac.users.role.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      104 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.status.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      112 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      161 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.backups.download',
          ),
          1 => 
          array (
            0 => 'databaseBackup',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      169 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.settings.backups.destroy',
          ),
          1 => 
          array (
            0 => 'databaseBackup',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      205 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-receipts.show',
          ),
          1 => 
          array (
            0 => 'salaryReceipt',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      224 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-receipts.export.pdf',
          ),
          1 => 
          array (
            0 => 'salaryReceipt',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      232 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-receipts.destroy',
          ),
          1 => 
          array (
            0 => 'salaryReceipt',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      276 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.edit',
          ),
          1 => 
          array (
            0 => 'employeeBonus',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      284 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.update',
          ),
          1 => 
          array (
            0 => 'employeeBonus',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-bonuses.destroy',
          ),
          1 => 
          array (
            0 => 'employeeBonus',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      329 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.checklists.show',
          ),
          1 => 
          array (
            0 => 'checklist',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.checklists.update',
          ),
          1 => 
          array (
            0 => 'checklist',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      364 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.client-ratings.show',
          ),
          1 => 
          array (
            0 => 'clientRating',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.client-ratings.update',
          ),
          1 => 
          array (
            0 => 'clientRating',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      379 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.client-ratings.status.update',
          ),
          1 => 
          array (
            0 => 'clientRating',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      387 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.client-ratings.destroy',
          ),
          1 => 
          array (
            0 => 'clientRating',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      426 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.service-types.show',
          ),
          1 => 
          array (
            0 => 'serviceType',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.service-types.update',
          ),
          1 => 
          array (
            0 => 'serviceType',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      441 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.service-types.status.update',
          ),
          1 => 
          array (
            0 => 'serviceType',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      449 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.service-types.destroy',
          ),
          1 => 
          array (
            0 => 'serviceType',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      482 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.safety-types.show',
          ),
          1 => 
          array (
            0 => 'safetyType',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.safety-types.update',
          ),
          1 => 
          array (
            0 => 'safetyType',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      497 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.safety-types.status.update',
          ),
          1 => 
          array (
            0 => 'safetyType',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      505 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.safety-types.destroy',
          ),
          1 => 
          array (
            0 => 'safetyType',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      543 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.equipment-types.show',
          ),
          1 => 
          array (
            0 => 'equipmentType',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.equipment-types.update',
          ),
          1 => 
          array (
            0 => 'equipmentType',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      558 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.equipment-types.status.update',
          ),
          1 => 
          array (
            0 => 'equipmentType',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      566 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.equipment-types.destroy',
          ),
          1 => 
          array (
            0 => 'equipmentType',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      598 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.recurrences.show',
          ),
          1 => 
          array (
            0 => 'recurrence',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.recurrences.update',
          ),
          1 => 
          array (
            0 => 'recurrence',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      613 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.recurrences.status.update',
          ),
          1 => 
          array (
            0 => 'recurrence',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      621 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.recurrences.destroy',
          ),
          1 => 
          array (
            0 => 'recurrence',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      647 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.zones.show',
          ),
          1 => 
          array (
            0 => 'zone',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.zones.update',
          ),
          1 => 
          array (
            0 => 'zone',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      662 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.zones.status.update',
          ),
          1 => 
          array (
            0 => 'zone',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      670 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.zones.destroy',
          ),
          1 => 
          array (
            0 => 'zone',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      709 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.accounting-levels.show',
          ),
          1 => 
          array (
            0 => 'accountingLevel',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.accounting-levels.update',
          ),
          1 => 
          array (
            0 => 'accountingLevel',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      724 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.accounting-levels.status.update',
          ),
          1 => 
          array (
            0 => 'accountingLevel',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      732 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.accounting-levels.destroy',
          ),
          1 => 
          array (
            0 => 'accountingLevel',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      764 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.job-levels.show',
          ),
          1 => 
          array (
            0 => 'jobLevel',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.job-levels.update',
          ),
          1 => 
          array (
            0 => 'jobLevel',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      779 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.job-levels.status.update',
          ),
          1 => 
          array (
            0 => 'jobLevel',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      787 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.masters.job-levels.destroy',
          ),
          1 => 
          array (
            0 => 'jobLevel',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      833 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.detail',
          ),
          1 => 
          array (
            0 => 'contractor',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      845 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.jobs',
          ),
          1 => 
          array (
            0 => 'contractor',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      865 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.store',
          ),
          1 => 
          array (
            0 => 'contractor',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      885 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.show',
          ),
          1 => 
          array (
            0 => 'contractor',
            1 => 'contract',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.update',
          ),
          1 => 
          array (
            0 => 'contractor',
            1 => 'contract',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      903 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.status.update',
          ),
          1 => 
          array (
            0 => 'contractor',
            1 => 'contract',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      923 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.documents.store',
          ),
          1 => 
          array (
            0 => 'contractor',
            1 => 'contract',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      952 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.documents.download',
          ),
          1 => 
          array (
            0 => 'contractor',
            1 => 'contract',
            2 => 'document',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      960 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.contracts.documents.destroy',
          ),
          1 => 
          array (
            0 => 'contractor',
            1 => 'contract',
            2 => 'document',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      974 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.show',
          ),
          1 => 
          array (
            0 => 'contractor',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.contractors.update',
          ),
          1 => 
          array (
            0 => 'contractor',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1009 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.edit',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1022 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.jobs',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1058 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.documents.download',
          ),
          1 => 
          array (
            0 => 'client',
            1 => 'document',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1074 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.restore',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1084 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.update',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.destroy',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.show',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1116 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.show',
          ),
          1 => 
          array (
            0 => 'followup',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1130 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.edit',
          ),
          1 => 
          array (
            0 => 'followup',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1139 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.update',
          ),
          1 => 
          array (
            0 => 'followup',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.followups.destroy',
          ),
          1 => 
          array (
            0 => 'followup',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1172 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.show',
          ),
          1 => 
          array (
            0 => 'discussion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1189 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.edit',
          ),
          1 => 
          array (
            0 => 'discussion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1208 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.export-pdf',
          ),
          1 => 
          array (
            0 => 'discussion',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1218 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.update',
          ),
          1 => 
          array (
            0 => 'discussion',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.discussions.destroy',
          ),
          1 => 
          array (
            0 => 'discussion',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1253 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.edit',
          ),
          1 => 
          array (
            0 => 'lead',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1268 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.status.update',
          ),
          1 => 
          array (
            0 => 'lead',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1282 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.notes.store',
          ),
          1 => 
          array (
            0 => 'lead',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1292 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.update',
          ),
          1 => 
          array (
            0 => 'lead',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.show',
          ),
          1 => 
          array (
            0 => 'lead',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leads.destroy',
          ),
          1 => 
          array (
            0 => 'lead',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1329 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.edit',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1345 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.remarks.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1355 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1369 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.bulk.destroy',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1385 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.reorder',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1412 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.images.store',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1421 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.jobs.show',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1463 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.discussions.show',
          ),
          1 => 
          array (
            0 => 'discussion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1488 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.show',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1507 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.status.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1523 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.payment.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1546 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.consumed-time.update',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1574 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.images.before',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1592 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.images.before.destroy',
          ),
          1 => 
          array (
            0 => 'job',
            1 => 'imageId',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1610 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.images.after',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1628 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.images.after.destroy',
          ),
          1 => 
          array (
            0 => 'job',
            1 => 'imageId',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1645 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'mower.jobs.remark.store',
          ),
          1 => 
          array (
            0 => 'job',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1673 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local.upload',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'boost.browser-logs' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_boost/browser-logs',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:1598:"function (\\Illuminate\\Http\\Request $request) {
            $logs = $request->input(\'logs\', []);

            // Handle sendBeacon\'s text/plain content type.
            if (empty($logs) && ! $request->isJson()) {
                $decoded = json_decode($request->getContent(), true);
                $logs = $decoded[\'logs\'] ?? [];
            }

            /** @var Logger $logger */
            $logger = \\Illuminate\\Support\\Facades\\Log::channel(\'browser\');

            /**
             *  @var array{
             *      type: \'error\'|\'warn\'|\'info\'|\'log\'|\'table\'|\'window_error\'|\'uncaught_error\'|\'unhandled_rejection\',
             *      timestamp: string,
             *      data: array,
             *      url:string,
             *      userAgent:string
             *  } $log */
            foreach ($logs as $log) {
                $logger->write(
                    level: match ($log[\'type\']) {
                        \'warn\' => \'warning\',
                        \'log\', \'table\' => \'debug\',
                        \'window_error\', \'uncaught_error\', \'unhandled_rejection\' => \'error\',
                        default => $log[\'type\']
                    },
                    message: self::buildLogMessageFromData($log[\'data\']),
                    context: [
                        \'url\' => $log[\'url\'],
                        \'user_agent\' => $log[\'userAgent\'] ?: null,
                        \'timestamp\' => $log[\'timestamp\'] ?: now()->toIso8601String(),
                    ]
                );
            }

            return response()->json([\'status\' => \'logged\']);
        }";s:5:"scope";s:34:"Laravel\\Boost\\BoostServiceProvider";s:4:"this";N;s:4:"self";s:32:"0000000000000ab00000000000000000";}}',
        'as' => 'boost.browser-logs',
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XyKjE8qiYO88i24m' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:1132:"function (\\Illuminate\\Http\\Request $request) {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    $status = $exception ? 500 : 200;

                    if ($request->expectsJson()) {
                        return response()->json([
                            \'status\' => $exception ? \'down\' : \'up\',
                        ], $status);
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'/Users/narindersingh/gorilla/gorilla/vendor/laravel/framework/src/Illuminate/Foundation/Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $status);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"0000000000000afe0000000000000000";}}',
        'as' => 'generated::XyKjE8qiYO88i24m',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::aO0pnxZ5PnPspJYn' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'clear-cache',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:292:"function () {
    \\Illuminate\\Support\\Facades\\Artisan::call(\'cache:clear\');
    \\Illuminate\\Support\\Facades\\Artisan::call(\'config:clear\');
    \\Illuminate\\Support\\Facades\\Artisan::call(\'route:clear\');
    \\Illuminate\\Support\\Facades\\Artisan::call(\'view:clear\');

    return \'Cache Cleared\';
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b010000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::aO0pnxZ5PnPspJYn',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'home' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:65:"function () {
    return \\redirect()->route(\'dashboard.index\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000b030000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'home',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@create',
        'controller' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'login.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
          2 => 'throttle:10,1',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@store',
        'controller' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'login.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'forgot-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@create',
        'controller' => 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.request',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.email' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'forgot-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
          2 => 'throttle:5,15',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@store',
        'controller' => 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.email',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reset-password/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\NewPasswordController@create',
        'controller' => 'App\\Http\\Controllers\\Auth\\NewPasswordController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.reset',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'guest',
          2 => 'throttle:10,15',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\NewPasswordController@store',
        'controller' => 'App\\Http\\Controllers\\Auth\\NewPasswordController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\DashboardController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.daily-jobs-table' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/daily-jobs-table',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@dailyJobsTable',
        'controller' => 'App\\Http\\Controllers\\DashboardController@dailyJobsTable',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.daily-jobs-table',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.daily-leads-table' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/daily-leads-table',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@dailyLeadsTable',
        'controller' => 'App\\Http\\Controllers\\DashboardController@dailyLeadsTable',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.daily-leads-table',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.three-week-grid' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/three-week-grid',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@threeWeekGrid',
        'controller' => 'App\\Http\\Controllers\\DashboardController@threeWeekGrid',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.three-week-grid',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.preferences.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'dashboard/preferences',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@updatePreferences',
        'controller' => 'App\\Http\\Controllers\\DashboardController@updatePreferences',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.preferences.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.analytics.charts' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/analytics/charts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@analyticsCharts',
        'controller' => 'App\\Http\\Controllers\\DashboardController@analyticsCharts',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.analytics.charts',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.mower-performance-table' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/mower-performance-table',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@mowerPerformanceTable',
        'controller' => 'App\\Http\\Controllers\\DashboardController@mowerPerformanceTable',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.mower-performance-table',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'dashboard.hold-jobs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'dashboard/hold-jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:view_dashboard',
        ),
        'uses' => 'App\\Http\\Controllers\\DashboardController@holdJobs',
        'controller' => 'App\\Http\\Controllers\\DashboardController@holdJobs',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'dashboard.hold-jobs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@destroy',
        'controller' => 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'profile.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\ProfileController@edit',
        'controller' => 'App\\Http\\Controllers\\ProfileController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'profile.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\ProfileController@update',
        'controller' => 'App\\Http\\Controllers\\ProfileController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'profile.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'profile.password.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'profile/password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\ProfileController@updatePassword',
        'controller' => 'App\\Http\\Controllers\\ProfileController@updatePassword',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'profile.password.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@index',
        'controller' => 'App\\Http\\Controllers\\NotificationController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.unread' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'notifications/unread',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@unread',
        'controller' => 'App\\Http\\Controllers\\NotificationController@unread',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.unread',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'notifications.read' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'notifications/read',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@markRead',
        'controller' => 'App\\Http\\Controllers\\NotificationController@markRead',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'notifications.read',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.rbac.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/rbac',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RolePermissionController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolePermissionController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.rbac.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.rbac.users.role.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/rbac/users/{user}/role',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\RolePermissionController@updateUserRole',
        'controller' => 'App\\Http\\Controllers\\Admin\\RolePermissionController@updateUserRole',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.rbac.users.role.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/users/{user}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@updateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.status.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\UserManagementController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\UserManagementController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.users.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.activity-logs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/activity-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ActivityLogController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.activity-logs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingsController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingsController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SettingsController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\SettingsController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.backups.list' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings/backups',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\BackupController@list',
        'controller' => 'App\\Http\\Controllers\\Admin\\BackupController@list',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.backups.list',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.backups.trigger' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/settings/backups/trigger',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\BackupController@trigger',
        'controller' => 'App\\Http\\Controllers\\Admin\\BackupController@trigger',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.backups.trigger',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.backups.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/settings/backups/{databaseBackup}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\BackupController@download',
        'controller' => 'App\\Http\\Controllers\\Admin\\BackupController@download',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.backups.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.settings.backups.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/settings/backups/{databaseBackup}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_users',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\BackupController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\BackupController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.settings.backups.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-bonuses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.export.excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-bonuses/export/excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.export.excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.export.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-bonuses/export/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.export.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-bonuses/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_employee_bonuses',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employee-bonuses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_employee_bonuses',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-bonuses/{employeeBonus}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_employee_bonuses',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/employee-bonuses/{employeeBonus}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_employee_bonuses',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-bonuses.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/employee-bonuses/{employeeBonus}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_employee_bonuses',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\EmployeeBonusController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.employee-bonuses.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-receipts.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-receipts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-receipts.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-receipts.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-receipts/{salaryReceipt}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-receipts.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-receipts.export.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-receipts/{salaryReceipt}/export/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-receipts.export.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-calculator.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-calculator',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_salary_calculator',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryCalculatorController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryCalculatorController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-calculator.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-calculator.months' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-calculator/months',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_salary_calculator',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryCalculatorController@months',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryCalculatorController@months',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-calculator.months',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-calculator.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/salary-calculator',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_salary_calculator',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryCalculatorController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryCalculatorController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-calculator.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-receipts.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/salary-receipts/{salaryReceipt}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_salary_calculator',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\SalaryReceiptController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.salary-receipts.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.checklists.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/checklists',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@index',
        'as' => 'admin.masters.checklists.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.checklists.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/checklists',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@store',
        'as' => 'admin.masters.checklists.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.checklists.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/checklists/{checklist}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@show',
        'as' => 'admin.masters.checklists.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.checklists.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/checklists/{checklist}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ChecklistController@update',
        'as' => 'admin.masters.checklists.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.service-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/service-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@index',
        'as' => 'admin.masters.service-types.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.service-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/service-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@store',
        'as' => 'admin.masters.service-types.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.service-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/service-types/{serviceType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@show',
        'as' => 'admin.masters.service-types.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.service-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/service-types/{serviceType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@update',
        'as' => 'admin.masters.service-types.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.service-types.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/service-types/{serviceType}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@updateStatus',
        'as' => 'admin.masters.service-types.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.service-types.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/service-types/{serviceType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ServiceTypeController@destroy',
        'as' => 'admin.masters.service-types.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.equipment-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/equipment-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@index',
        'as' => 'admin.masters.equipment-types.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.equipment-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/equipment-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@store',
        'as' => 'admin.masters.equipment-types.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.equipment-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/equipment-types/{equipmentType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@show',
        'as' => 'admin.masters.equipment-types.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.equipment-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/equipment-types/{equipmentType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@update',
        'as' => 'admin.masters.equipment-types.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.equipment-types.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/equipment-types/{equipmentType}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@updateStatus',
        'as' => 'admin.masters.equipment-types.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.equipment-types.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/equipment-types/{equipmentType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\EquipmentTypeController@destroy',
        'as' => 'admin.masters.equipment-types.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.safety-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/safety-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@index',
        'as' => 'admin.masters.safety-types.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.safety-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/safety-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@store',
        'as' => 'admin.masters.safety-types.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.safety-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/safety-types/{safetyType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@show',
        'as' => 'admin.masters.safety-types.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.safety-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/safety-types/{safetyType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@update',
        'as' => 'admin.masters.safety-types.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.safety-types.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/safety-types/{safetyType}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@updateStatus',
        'as' => 'admin.masters.safety-types.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.safety-types.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/safety-types/{safetyType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\SafetyTypeController@destroy',
        'as' => 'admin.masters.safety-types.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.recurrences.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/recurrences',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@index',
        'as' => 'admin.masters.recurrences.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.recurrences.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/recurrences',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@store',
        'as' => 'admin.masters.recurrences.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.recurrences.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/recurrences/{recurrence}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@show',
        'as' => 'admin.masters.recurrences.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.recurrences.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/recurrences/{recurrence}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@update',
        'as' => 'admin.masters.recurrences.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.recurrences.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/recurrences/{recurrence}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@updateStatus',
        'as' => 'admin.masters.recurrences.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.recurrences.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/recurrences/{recurrence}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\RecurrenceController@destroy',
        'as' => 'admin.masters.recurrences.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.zones.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/zones',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@index',
        'as' => 'admin.masters.zones.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.zones.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/zones',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@store',
        'as' => 'admin.masters.zones.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.zones.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/zones/{zone}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@show',
        'as' => 'admin.masters.zones.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.zones.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/zones/{zone}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@update',
        'as' => 'admin.masters.zones.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.zones.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/zones/{zone}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@updateStatus',
        'as' => 'admin.masters.zones.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.zones.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/zones/{zone}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ZoneController@destroy',
        'as' => 'admin.masters.zones.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.accounting-levels.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/accounting-levels',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@index',
        'as' => 'admin.masters.accounting-levels.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.accounting-levels.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/accounting-levels',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@store',
        'as' => 'admin.masters.accounting-levels.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.accounting-levels.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/accounting-levels/{accountingLevel}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@show',
        'as' => 'admin.masters.accounting-levels.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.accounting-levels.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/accounting-levels/{accountingLevel}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@update',
        'as' => 'admin.masters.accounting-levels.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.accounting-levels.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/accounting-levels/{accountingLevel}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@updateStatus',
        'as' => 'admin.masters.accounting-levels.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.accounting-levels.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/accounting-levels/{accountingLevel}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\AccountingLevelController@destroy',
        'as' => 'admin.masters.accounting-levels.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.job-levels.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/job-levels',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@index',
        'as' => 'admin.masters.job-levels.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.job-levels.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/job-levels',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@store',
        'as' => 'admin.masters.job-levels.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.job-levels.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/job-levels/{jobLevel}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@show',
        'as' => 'admin.masters.job-levels.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.job-levels.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/job-levels/{jobLevel}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@update',
        'as' => 'admin.masters.job-levels.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.job-levels.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/job-levels/{jobLevel}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@updateStatus',
        'as' => 'admin.masters.job-levels.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.job-levels.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/job-levels/{jobLevel}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\JobLevelController@destroy',
        'as' => 'admin.masters.job-levels.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.client-ratings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/client-ratings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@index',
        'as' => 'admin.masters.client-ratings.index',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.client-ratings.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/masters/client-ratings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@store',
        'as' => 'admin.masters.client-ratings.store',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.client-ratings.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/masters/client-ratings/{clientRating}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@show',
        'as' => 'admin.masters.client-ratings.show',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.client-ratings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/client-ratings/{clientRating}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@update',
        'as' => 'admin.masters.client-ratings.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.client-ratings.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/masters/client-ratings/{clientRating}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@updateStatus',
        'as' => 'admin.masters.client-ratings.status.update',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.masters.client-ratings.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/masters/client-ratings/{clientRating}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_masters',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Masters\\ClientRatingController@destroy',
        'as' => 'admin.masters.client-ratings.destroy',
        'namespace' => NULL,
        'prefix' => '/admin/masters',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contractors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@index',
        'as' => 'admin.contractors.index',
        'namespace' => NULL,
        'prefix' => '/admin/contractors',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/contractors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@store',
        'as' => 'admin.contractors.store',
        'namespace' => NULL,
        'prefix' => '/admin/contractors',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contractors/{contractor}/detail',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@detail',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@detail',
        'as' => 'admin.contractors.detail',
        'namespace' => NULL,
        'prefix' => '/admin/contractors',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.jobs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contractors/{contractor}/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@jobs',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@jobs',
        'as' => 'admin.contractors.jobs',
        'namespace' => NULL,
        'prefix' => '/admin/contractors',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contractors/{contractor}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@show',
        'as' => 'admin.contractors.show',
        'namespace' => NULL,
        'prefix' => '/admin/contractors',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/contractors/{contractor}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractorController@update',
        'as' => 'admin.contractors.update',
        'namespace' => NULL,
        'prefix' => '/admin/contractors',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@store',
        'as' => 'admin.contractors.contracts.store',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts/{contract}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@show',
        'as' => 'admin.contractors.contracts.show',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts/{contract}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@update',
        'as' => 'admin.contractors.contracts.update',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts/{contract}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractController@updateStatus',
        'as' => 'admin.contractors.contracts.status.update',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.documents.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts/{contract}/documents',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractDocumentController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractDocumentController@store',
        'as' => 'admin.contractors.contracts.documents.store',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts/{contract}/documents',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.documents.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts/{contract}/documents/{document}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractDocumentController@download',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractDocumentController@download',
        'as' => 'admin.contractors.contracts.documents.download',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts/{contract}/documents',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.contractors.contracts.documents.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/contractors/{contractor}/contracts/{contract}/documents/{document}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_contractors',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractDocumentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\Contractors\\ContractDocumentController@destroy',
        'as' => 'admin.contractors.contracts.documents.destroy',
        'namespace' => NULL,
        'prefix' => 'admin/contractors/{contractor}/contracts/{contract}/documents',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.form-options' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups/form-options',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@formOptionsJson',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@formOptionsJson',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.followups.form-options',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.for-followable' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups/for-followable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@forFollowableJson',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@forFollowableJson',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.followups.for-followable',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.search-followable' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups/search-followable',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@searchFollowableJson',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@searchFollowableJson',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.followups.search-followable',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/followups',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups/{followup}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/followups/{followup}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/followups/{followup}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.followups.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/followups/{followup}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_followups',
        ),
        'as' => 'admin.followups.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\FollowupController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\FollowupController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/discussions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.index',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/discussions/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.create',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/discussions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.store',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/discussions/{discussion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.show',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/discussions/{discussion}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.edit',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/discussions/{discussion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.update',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/discussions/{discussion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'as' => 'admin.discussions.destroy',
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.discussions.export-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/discussions/{discussion}/export-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_discussions',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\DiscussionController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\DiscussionController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.discussions.export-pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.converted' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/converted',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@converted',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@converted',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.converted',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leads',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.export.excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/export/excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.export.excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.export.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/export/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.export.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.import.sample' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/import/sample',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@downloadImportSample',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@downloadImportSample',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.import.sample',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.import' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leads/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@import',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.import',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/{lead}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/leads/{lead}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/leads/{lead}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@updateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.status.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.notes.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leads/{lead}/notes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@addNote',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@addNote',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.notes.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leads/{lead}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leads.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/leads/{lead}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_leads',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\LeadManagementController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.leads.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.import.sample' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/import/sample',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@downloadImportSample',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@downloadImportSample',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.import.sample',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.import' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients/import',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@import',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@import',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.import',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.export.excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/export/excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.export.excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.export.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/export/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.export.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.reschedule' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients/reschedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@bulkReschedule',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@bulkReschedule',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.reschedule',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.hold' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients/hold',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@bulkHold',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@bulkHold',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.hold',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/{client}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.jobs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/{client}/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@jobsTab',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@jobsTab',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.jobs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.documents.download' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/{client}/documents/{document}/download',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@downloadDocument',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@downloadDocument',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.documents.download',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/clients/{client}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/clients/{client}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@destroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@destroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients/{client}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@restore',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@restore',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/{client}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:manage_customers',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\ClientManagementController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.clients.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@create',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@create',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.create',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.mower-suggestions' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/mower-suggestions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@mowerSuggestions',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@mowerSuggestions',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.mower-suggestions',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.mower-workloads' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/mower-workloads',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@mowerWorkloads',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@mowerWorkloads',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.mower-workloads',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.client-remarks' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/client-remarks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@clientRemarks',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@clientRemarks',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.client-remarks',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.client-history' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/client-history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@clientHistory',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@clientHistory',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.client-history',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.active-contracts' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/active-contracts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@activeContracts',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@activeContracts',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.active-contracts',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@store',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@store',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/{job}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@edit',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@edit',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.edit',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@update',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@update',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.remarks.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'admin/jobs/{job}/remarks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@updateRemarks',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@updateRemarks',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.remarks.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.assign' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/bulk/assign',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkAssignEmployees',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkAssignEmployees',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.assign',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.contract' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/bulk/contract',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkAssignContract',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkAssignContract',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.contract',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/bulk/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkUpdateStatus',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkUpdateStatus',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.status.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.verify' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/bulk/verify',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkVerify',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkVerify',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.verify',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.schedule' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/bulk/schedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkScheduleJobs',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkScheduleJobs',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.schedule',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/jobs/bulk',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkDestroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkDestroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/bulk/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkRestore',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkRestore',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.bulk.force-destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/jobs/bulk/force',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkForceDestroy',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@bulkForceDestroy',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.bulk.force-destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@reorder',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@reorder',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.reorder',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.images.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/jobs/{job}/images',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
          4 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@uploadImages',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@uploadImages',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.images.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maps.optimize' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maps/optimize',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@optimize',
        'controller' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@optimize',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.maps.optimize',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maps.assign-nearest' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/maps/assign-nearest',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.permission:assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@assignNearest',
        'controller' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@assignNearest',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.maps.assign-nearest',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.export.excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/export/excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@exportExcel',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@exportExcel',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.export.excel',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.export.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/export/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@exportPdf',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.export.pdf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\JobManagementController@show',
        'controller' => 'App\\Http\\Controllers\\Admin\\JobManagementController@show',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.jobs.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maps.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/maps',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@index',
        'controller' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.maps.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.maps.jobs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/maps/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@jobs',
        'controller' => 'App\\Http\\Controllers\\Admin\\MapRoutingController@jobs',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'admin.maps.jobs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'employee.mobile.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
        2 => 'POST',
        3 => 'PUT',
        4 => 'PATCH',
        5 => 'DELETE',
        6 => 'OPTIONS',
      ),
      'uri' => 'employee/mobile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => '\\Illuminate\\Routing\\RedirectController@__invoke',
        'controller' => '\\Illuminate\\Routing\\RedirectController',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'employee.mobile.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
        'destination' => '/mower',
        'status' => 302,
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.checklist.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/checklist',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\ChecklistController@index',
        'controller' => 'App\\Http\\Controllers\\Mower\\ChecklistController@index',
        'as' => 'mower.checklist.index',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.checklist.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mower/checklist',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\ChecklistController@submit',
        'controller' => 'App\\Http\\Controllers\\Mower\\ChecklistController@submit',
        'as' => 'mower.checklist.submit',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@index',
        'as' => 'mower.index',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.map' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/map',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@map',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@map',
        'as' => 'mower.map',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.notifications.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\NotificationController@mowerIndex',
        'controller' => 'App\\Http\\Controllers\\NotificationController@mowerIndex',
        'as' => 'mower.notifications.index',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.discussions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/discussions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDiscussionController@index',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDiscussionController@index',
        'as' => 'mower.discussions.index',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.discussions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/discussions/{discussion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDiscussionController@show',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDiscussionController@show',
        'as' => 'mower.discussions.show',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.clients.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/clients/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerClientController@create',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerClientController@create',
        'as' => 'mower.clients.create',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.clients.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mower/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerClientController@store',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerClientController@store',
        'as' => 'mower.clients.store',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@show',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@show',
        'as' => 'mower.jobs.show',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'mower/jobs/{job}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@update',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@update',
        'as' => 'mower.jobs.update',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.status.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'mower/jobs/{job}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@updateStatus',
        'as' => 'mower.jobs.status.update',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.payment.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'mower/jobs/{job}/payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@updatePayment',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@updatePayment',
        'as' => 'mower.jobs.payment.update',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.consumed-time.update' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'mower/jobs/{job}/consumed-time',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@updateConsumedTime',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@updateConsumedTime',
        'as' => 'mower.jobs.consumed-time.update',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.images.before' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mower/jobs/{job}/images/before',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@uploadBefore',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@uploadBefore',
        'as' => 'mower.jobs.images.before',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.images.after' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mower/jobs/{job}/images/after',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@uploadAfter',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@uploadAfter',
        'as' => 'mower.jobs.images.after',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.images.before.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'mower/jobs/{job}/images/before/{imageId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@deleteBefore',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@deleteBefore',
        'as' => 'mower.jobs.images.before.destroy',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.images.after.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'mower/jobs/{job}/images/after/{imageId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@deleteAfter',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@deleteAfter',
        'as' => 'mower.jobs.images.after.destroy',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.jobs.remark.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'mower/jobs/{job}/remark',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@storeRemark',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@storeRemark',
        'as' => 'mower.jobs.remark.store',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'mower.export-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'mower/export-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:manage_jobs,assign_jobs',
          4 => 'mower.checklist',
          5 => 'crm.permission:upload_job_images',
        ),
        'uses' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Mower\\MowerDashboardController@exportPdf',
        'as' => 'mower.export-pdf',
        'namespace' => NULL,
        'prefix' => '/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.mower.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/mower',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_mower_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\MowerReportController@index',
        'controller' => 'App\\Http\\Controllers\\Report\\MowerReportController@index',
        'as' => 'reports.mower.index',
        'namespace' => NULL,
        'prefix' => 'reports/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.mower.report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/mower/report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_mower_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\MowerReportController@report',
        'controller' => 'App\\Http\\Controllers\\Report\\MowerReportController@report',
        'as' => 'reports.mower.report',
        'namespace' => NULL,
        'prefix' => 'reports/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.mower.jobs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/mower/jobs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_mower_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\MowerReportController@jobs',
        'controller' => 'App\\Http\\Controllers\\Report\\MowerReportController@jobs',
        'as' => 'reports.mower.jobs',
        'namespace' => NULL,
        'prefix' => 'reports/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.mower.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/mower/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_mower_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\MowerReportController@export',
        'controller' => 'App\\Http\\Controllers\\Report\\MowerReportController@export',
        'as' => 'reports.mower.export',
        'namespace' => NULL,
        'prefix' => 'reports/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.mower.export-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/mower/export-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_mower_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\MowerReportController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Report\\MowerReportController@exportPdf',
        'as' => 'reports.mower.export-pdf',
        'namespace' => NULL,
        'prefix' => 'reports/mower',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.checklist.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/checklist',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_checklist_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@index',
        'controller' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@index',
        'as' => 'reports.checklist.index',
        'namespace' => NULL,
        'prefix' => 'reports/checklist',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.checklist.report' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/checklist/report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_checklist_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@report',
        'controller' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@report',
        'as' => 'reports.checklist.report',
        'namespace' => NULL,
        'prefix' => 'reports/checklist',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.checklist.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/checklist/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_checklist_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@export',
        'controller' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@export',
        'as' => 'reports.checklist.export',
        'namespace' => NULL,
        'prefix' => 'reports/checklist',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'reports.checklist.export-pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'reports/checklist/export-pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth',
          2 => 'active_user',
          3 => 'crm.any_permission:view_checklist_report',
        ),
        'uses' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@exportPdf',
        'controller' => 'App\\Http\\Controllers\\Report\\ChecklistReportController@exportPdf',
        'as' => 'reports.checklist.export-pdf',
        'namespace' => NULL,
        'prefix' => 'reports/checklist',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:56:"/Users/narindersingh/gorilla/gorilla/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:0;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"0000000000000b050000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local.upload' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:56:"/Users/narindersingh/gorilla/gorilla/storage/app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:0;}s:8:"function";s:325:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ReceiveFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"0000000000000b370000000000000000";}}',
        'as' => 'storage.local.upload',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
