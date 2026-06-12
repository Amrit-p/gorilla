<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJobIsNotVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $job = $request->route('job');

        if ($job && $job->isVerified() && ! $request->isMethod('GET')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'This job has been verified and cannot be modified.'], 403);
            }

            return redirect()->back()->with('error', 'This job has been verified and cannot be modified.');
        }

        return $next($request);
    }
}
