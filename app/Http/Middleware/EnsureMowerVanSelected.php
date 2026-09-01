<?php

namespace App\Http\Middleware;

use App\Models\Van;
use App\Models\VanAssignment;
use App\Support\CrmRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMowerVanSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(CrmRoles::MOWER)) {
            return $next($request);
        }

        // Nothing to enforce until the admin has set up at least one van.
        if (! Van::query()->active()->exists()) {
            return $next($request);
        }

        $hasVanToday = VanAssignment::query()
            ->where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->exists();

        if (! $hasVanToday) {
            return redirect()->route('mower.van.select');
        }

        return $next($request);
    }
}
