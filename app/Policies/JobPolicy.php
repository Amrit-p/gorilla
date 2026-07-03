<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use App\Support\CrmPermissions;
use App\Support\CrmRoles;

class JobPolicy
{
    public function viewAny(User $user): bool
    {
        return CrmPermissions::canViewJobs($user);
    }

    public function view(User $user, Job $job): bool
    {
        return CrmPermissions::canViewJobs($user);
    }

    public function create(User $user): bool
    {
        return CrmPermissions::canCreateJobs($user);
    }

    public function update(User $user, Job $job): bool
    {
        return CrmPermissions::canManageJobRecords($user) || CrmPermissions::canAssignJobs($user);
    }

    public function delete(User $user, Job $job): bool
    {
        return CrmPermissions::canManageJobRecords($user);
    }

    public function restore(User $user, Job $job): bool
    {
        return CrmPermissions::canManageJobRecords($user);
    }

    public function forceDelete(User $user, Job $job): bool
    {
        return CrmPermissions::canManageJobRecords($user);
    }

    public function assign(User $user, Job $job): bool
    {
        return CrmPermissions::canAssignJobs($user);
    }

    public function transitionStatus(User $user, Job $job): bool
    {
        return CrmPermissions::canAssignJobs($user);
    }

    public function verify(User $user, Job $job): bool
    {
        return CrmPermissions::canVerifyJobs($user);
    }

    public function uploadImages(User $user, Job $job): bool
    {
        if (! CrmPermissions::canUploadJobImages($user)) {
            return false;
        }

        if ($user->hasRole(CrmRoles::OFFICE_MANAGER)) {
            return true;
        }

        return $job->assignedEmployees()->where('users.id', $user->id)->exists()
            || (int) $job->done_by_user_id === $user->id;
    }

    public function deleteJobImages(User $user, Job $job): bool
    {
        return $this->uploadImages($user, $job);
    }

    public function employeeUpdateStatus(User $user, Job $job): bool
    {
        if (! CrmPermissions::canViewJobs($user)) {
            return false;
        }

        if ($user->hasRole(CrmRoles::OFFICE_MANAGER)) {
            return true;
        }

        return $job->assignedEmployees()->where('users.id', $user->id)->exists()
            || (int) $job->done_by_user_id === $user->id;
    }
}
