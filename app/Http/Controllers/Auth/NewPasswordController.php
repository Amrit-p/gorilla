<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Show reset password form from email link.
     */
    public function create(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Persist new password after token validation.
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = $this->authService->resetPassword($request);

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
