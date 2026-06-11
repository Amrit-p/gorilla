<?php

namespace App\Http\Controllers\Mower;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MowerDiscussionController extends Controller
{
    public function index(Request $request): View
    {
        $discussions = Discussion::with(['sections'])
            ->where('worker_id', $request->user()->id)
            ->orderByDesc('date')
            ->get();

        $grouped = $discussions->groupBy(fn ($d) => $d->date->toDateString());

        return view('mower.discussions.index', compact('discussions', 'grouped'));
    }

    public function show(Request $request, Discussion $discussion): View
    {
        abort_unless($discussion->worker_id === $request->user()->id, 403);

        $discussion->load(['sections.attachments', 'createdBy']);

        return view('mower.discussions.show', compact('discussion'));
    }
}
