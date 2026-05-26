<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Attempt login and return whether auth succeeded.
     */
    public function attemptLogin(LoginRequest $request): bool
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->safe()->only(['email', 'password']);
        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            // Count failed attempts so LoginRequest::ensureIsNotRateLimited() can lock the form.
            RateLimiter::hit($request->throttleKey());

            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        // Optional safety check for deactivated users.
        if (array_key_exists('is_active', $user->getAttributes()) && ! $user->is_active) {
            Auth::logout();
            RateLimiter::hit($request->throttleKey());

            return false;
        }

        RateLimiter::clear($request->throttleKey());

        $request->session()->regenerate();
        $this->activityLogService->log($user, 'auth.login', 'User logged in successfully.');

        return true;
    }

    /**
     * Send reset link using Laravel password broker.
     */
    public function sendPasswordResetLink(ForgotPasswordRequest $request): string
    {
        return Password::sendResetLink($request->safe()->only('email'));
    }

    /**
     * Reset password and return broker status code.
     */
    public function resetPassword(ResetPasswordRequest $request): string
    {
        return Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                $this->activityLogService->log($user, 'auth.password_reset', 'User reset account password.');
            }
        );
    }

    /**
     * Logout helper that also records activity.
     */
    public function logout(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        $this->activityLogService->log($user, 'auth.logout', 'User logged out.');

        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
