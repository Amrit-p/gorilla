<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Show profile management screen.
     */
    public function edit(): View
    {
        return view('profile.edit');
    }

    /**
     * Update user profile details.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse|RedirectResponse
    {
        $this->profileService->updateProfile($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
            ]);
        }

        return back()->with('status', 'Profile updated successfully.');
    }

    /**
     * Change user password securely.
     */
    public function updatePassword(ChangePasswordRequest $request): JsonResponse|RedirectResponse
    {
        $this->profileService->changePassword($request->user(), $request->validated('password'));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password changed successfully.',
            ]);
        }

        return back()->with('status', 'Password changed successfully.');
    }
}
