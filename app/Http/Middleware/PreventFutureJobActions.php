<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventFutureJobActions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $job = $request->route('job');

        if ($job) {
            if ($job->isVerified() && ! $request->isMethod('GET')) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'This job has been verified and cannot be modified.'], 403);
                }

                return redirect()->route('mower.index')->with('error', 'This job has been verified and cannot be modified.');
            }

            if (isset($job->scheduled_date)) {
                try {
                    $scheduled = Carbon::parse($job->scheduled_date)->startOfDay();
                    if ($scheduled->gt(Carbon::today())) {
                        if ($request->expectsJson() || $request->ajax()) {
                            return response()->json(['message' => 'You cannot access or modify jobs scheduled in the future.'], 403);
                        }

                        return redirect()->route('mower.index')->with('error', 'You cannot access or modify jobs scheduled in the future.');
                    }
                } catch (\Exception) {
                    // If parsing fails, allow the request to continue and let other layers handle it.
                }
            }
        }

        return $next($request);
    }
}
