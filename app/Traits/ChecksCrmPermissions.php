<?php

namespace App\Traits;

use App\Support\CrmPermissions;

/**
 * Convenience methods on the authenticated User model.
 */
trait ChecksCrmPermissions
{
    public function canViewDashboard(): bool
    {
        return CrmPermissions::canViewDashboard($this);
    }

    public function canManageLeads(): bool
    {
        return CrmPermissions::canManageLeads($this);
    }

    public function canManageCustomers(): bool
    {
        return CrmPermissions::canManageCustomers($this);
    }

    public function canViewJobs(): bool
    {
        return CrmPermissions::canViewJobs($this);
    }

    public function canManageJobRecords(): bool
    {
        return CrmPermissions::canManageJobRecords($this);
    }

    public function canAssignJobs(): bool
    {
        return CrmPermissions::canAssignJobs($this);
    }

    public function canManageUsers(): bool
    {
        return CrmPermissions::canManageUsers($this);
    }

    public function canUploadJobImages(): bool
    {
        return CrmPermissions::canUploadJobImages($this);
    }

    public function canManageFollowups(): bool
    {
        return CrmPermissions::canManageFollowups($this);
    }

    public function canVerifyJobs(): bool
    {
        return CrmPermissions::canVerifyJobs($this);
    }
}
