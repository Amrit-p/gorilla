<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {
    }

    /**
     * Update editable profile fields.
     */
    public function updateProfile(User $user, array $data): void
    {
        $user->fill($data);
        $user->save();

        $this->activityLogService->log($user, 'profile.updated', 'User updated profile information.');
    }

    /**
     * Change authenticated user's password.
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $user->save();

        $this->activityLogService->log($user, 'profile.password_changed', 'User changed account password.');
    }
}
