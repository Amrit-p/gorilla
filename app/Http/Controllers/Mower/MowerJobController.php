<?php

namespace App\Http\Controllers\Mower;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mower\StoreMowerJobRequest;
use App\Services\JobManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MowerJobController extends Controller
{
    public function __construct(
        private readonly JobManagementService $jobManagementService
    ) {}

    public function create(Request $request): View
    {
        $clientId = $request->integer('client_id') ?: null;

        return view('mower.jobs.create', $this->jobManagementService->formOptions($clientId));
    }

    public function store(StoreMowerJobRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['done_by_user_id'] = $request->user()->id;

        if (empty($validated['employee_ids'])) {
            $validated['employee_ids'] = [$request->user()->id];
        }

        $job = $this->jobManagementService->createJob(
            $request->user(),
            $validated,
            $request->file('images', [])
        );

        if (! $job) {
            return back()->withInput()->with('error', 'Failed to create job. Please try again.');
        }

        return redirect()
            ->route('mower.index')
            ->with('success', 'Job created successfully.');
    }
}
