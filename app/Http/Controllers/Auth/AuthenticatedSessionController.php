<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Show login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (! $this->authService->attemptLogin($request)) {
            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        return redirect()->intended(route('dashboard.index'));
    }

    /**
     * Logout user from current web session.
     */
    public function destroy(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login');
    }
}
