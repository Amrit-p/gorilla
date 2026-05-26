<?php

use App\Support\CrmPermissions;
use App\Support\CrmRoles;

return [
    'super_admin' => CrmRoles::OFFICE_MANAGER,

    'assignable' => CrmPermissions::assignableRoleLabels(),

    'rbac_assignable' => CrmPermissions::rbacAssignableRoleLabels(),

    'legacy_names' => [
        'Super Admin' => CrmRoles::OFFICE_MANAGER,
        'Employee' => CrmRoles::MOWER,
        'Sales' => CrmRoles::SALES_MANAGER,
        'Manager' => CrmRoles::OFFICE_MANAGER,
        'Mowers' => CrmRoles::MOWER,
        'Sales Person' => CrmRoles::SALES_MANAGER,
    ],
];
