<?php

namespace App\Http\Controllers\Mower;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\MowerChecklistSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecklistController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $today   = now()->toDateString();
        $user    = $request->user();
        $checklist = Checklist::safetyChecklist();

        $activePointIds = $checklist ? $checklist->points->pluck('id') : collect();

        if ($activePointIds->isNotEmpty()) {
            $submittedPointIds = MowerChecklistSubmission::query()
                ->where('user_id', '=', $user->id)
                ->where('date', '=', $today)
                ->pluck('checklist_point_id');

            if ($activePointIds->diff($submittedPointIds)->isEmpty()) {
                return redirect()->route('mower.index');
            }
        }

        return view('mower.checklist', [
            'checklists' => $checklist ? collect([$checklist]) : collect(),
            'today'      => $today,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'point_ids'    => ['required', 'array'],
            'point_ids.*'  => ['integer', 'exists:checklist_points,id'],
            'acknowledged' => ['required', 'accepted'],
        ], [
            'acknowledged.required' => 'You must confirm the declaration before proceeding.',
            'acknowledged.accepted'  => 'You must confirm the declaration before proceeding.',
        ]);

        $checklist      = Checklist::safetyChecklist();
        $activePointIds = $checklist ? $checklist->points->pluck('id') : collect();
        $submittedIds   = collect($validated['point_ids']);
        $missing        = $activePointIds->diff($submittedIds);

        if ($missing->isNotEmpty()) {
            return back()->withErrors(['point_ids' => 'Please check all points before proceeding.']);
        }

        $today  = now()->toDateString();
        $userId = $request->user()->id;

        foreach ($submittedIds as $pointId) {
            MowerChecklistSubmission::firstOrCreate([
                'user_id'            => $userId,
                'checklist_point_id' => $pointId,
                'date'               => $today,
            ]);
        }

        return redirect()->route('mower.index');
    }
}
