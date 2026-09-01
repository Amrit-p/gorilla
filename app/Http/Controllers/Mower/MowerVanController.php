<?php

namespace App\Http\Controllers\Mower;

use App\Http\Controllers\Controller;
use App\Models\Van;
use App\Models\VanAssignment;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MowerVanController extends Controller
{
    public function select(Request $request): View
    {
        $today = now()->toDateString();
        $userId = $request->user()->id;

        $vans = Van::query()->active()->ordered()->get(['id', 'name']);

        $assignments = VanAssignment::query()
            ->where('date', $today)
            ->with('user:id,name')
            ->get()
            ->keyBy('van_id');

        $currentVanId = optional($assignments->firstWhere('user_id', $userId))->van_id;

        return view('mower.van.select', [
            'vans' => $vans,
            'assignments' => $assignments,
            'currentVanId' => $currentVanId,
            'today' => $today,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'van_id' => ['required', 'integer', 'exists:vans,id'],
        ]);

        $today = now()->toDateString();
        $userId = $request->user()->id;
        $vanId = (int) $validated['van_id'];

        $takenByOther = VanAssignment::query()
            ->where('date', $today)
            ->where('van_id', $vanId)
            ->where('user_id', '!=', $userId)
            ->exists();

        if ($takenByOther) {
            return back()->withErrors(['van_id' => 'That van has already been taken by another mower today. Please pick a different one.']);
        }

        try {
            VanAssignment::updateOrCreate(
                ['user_id' => $userId, 'date' => $today],
                ['van_id' => $vanId],
            );
        } catch (QueryException) {
            // Unique(van_id, date) race: someone grabbed it a moment ago.
            return back()->withErrors(['van_id' => 'That van was just taken. Please pick a different one.']);
        }

        return redirect()->route('mower.index')->with('success', 'Van selected for today.');
    }
}
