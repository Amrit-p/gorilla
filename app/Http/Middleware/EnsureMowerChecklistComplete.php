<?php

namespace App\Http\Middleware;

use App\Models\Checklist;
use App\Models\MowerChecklistSubmission;
use App\Support\CrmRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMowerChecklistComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(CrmRoles::MOWER)) {
            return $next($request);
        }
        $checklist = Checklist::safetyChecklist();

        if (! $checklist) {
            abort(503, 'Safety checklist is not configured.');
        }

        $activePointIds = $checklist->points->pluck('id');

        if ($activePointIds->isEmpty()) {
            abort(503, 'Safety checklist has no active points configured.');
        }

        $today = now()->toDateString();

        $submittedPointIds = MowerChecklistSubmission::where('user_id', $user->id)
            ->where('date', $today)
            ->pluck('checklist_point_id');

        if ($activePointIds->diff($submittedPointIds)->isNotEmpty()) {
            return redirect()->route('mower.checklist.index');
        }

        return $next($request);
    }
}
