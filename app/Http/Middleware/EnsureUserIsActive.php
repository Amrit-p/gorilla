<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block deactivated accounts from authenticated areas (logout + friendly message).
 * Login path already rejects inactive users in AuthService.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && array_key_exists('is_active', $user->getAttributes()) && ! $user->is_active) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your account has been deactivated. Contact an administrator.']);
        }

        return $next($request);
    }
}
