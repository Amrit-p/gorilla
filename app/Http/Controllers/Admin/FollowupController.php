<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Followups\StoreFollowupRequest;
use App\Http\Requests\Admin\Followups\UpdateFollowupRequest;
use App\Models\Followup;
use App\Services\FollowupService;
use App\Support\CrmPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowupController extends Controller
{
    public function __construct(
        private readonly FollowupService $followupService
    ) {}

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->can(CrmPermissions::MANAGE_FOLLOWUPS), 403);
    }

    public function formOptionsJson(): JsonResponse
    {
        abort_unless(auth()->user()->can(CrmPermissions::MANAGE_FOLLOWUPS), 403);

        $options = $this->followupService->formOptions();

        return response()->json([
            'statuses' => array_map(fn ($s) => ['value' => $s->value, 'label' => $s->label()], $options['statuses']),
        ]);
    }

    public function searchFollowableJson(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can(CrmPermissions::MANAGE_FOLLOWUPS), 403);

        $type = (string) $request->query('type', '');

        abort_unless(in_array($type, FollowupService::ALLOWED_FOLLOWABLE_TYPES, true), 422);

        return response()->json([
            'results' => $this->followupService->searchFollowable($type, (string) $request->query('q', '')),
        ]);
    }

    public function forFollowableJson(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can(CrmPermissions::MANAGE_FOLLOWUPS), 403);

        $type = $request->query('type', '');
        $id = (int) $request->query('id', 0);

        abort_unless(
            in_array($type, FollowupService::ALLOWED_FOLLOWABLE_TYPES, true) && $id > 0,
            422
        );

        return response()->json([
            'followups' => $this->followupService->followupsForFollowable($type, $id),
        ]);
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeAdmin();

        $filters = $request->only([
            'search',
            'status',
            'followable_type',
            'next_followup_from',
            'next_followup_to',
        ]);
        $followups = $this->followupService->paginatedFollowups($filters);
        $options = $this->followupService->formOptions();

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.followups.partials.list', ['followups' => $followups]);
        }

        return view('admin.followups.index', compact('followups', 'filters', 'options'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAdmin();

        $options = $this->followupService->formOptions();

        $followableType = $request->query('followable_type');
        $followableId = $request->query('followable_id');

        return view('admin.followups.create', compact('options', 'followableType', 'followableId'));
    }

    public function store(StoreFollowupRequest $request): JsonResponse|RedirectResponse
    {
        $followup = $this->followupService->createFollowup(auth()->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Follow-up saved.', 'id' => $followup->id]);
        }

        return redirect()->route('admin.followups.show', $followup)
            ->with('success', 'Follow-up created.');
    }

    public function show(Followup $followup): View
    {
        $this->authorizeAdmin();

        $followup->load(['followable', 'createdBy']);

        return view('admin.followups.show', compact('followup'));
    }

    public function edit(Followup $followup): View
    {
        $this->authorizeAdmin();

        $options = $this->followupService->formOptions();

        return view('admin.followups.edit', compact('followup', 'options'));
    }

    public function update(UpdateFollowupRequest $request, Followup $followup): JsonResponse|RedirectResponse
    {
        $this->followupService->updateFollowup(auth()->user(), $followup, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Follow-up updated.', 'id' => $followup->id]);
        }

        return redirect()->route('admin.followups.show', $followup)
            ->with('success', 'Follow-up updated.');
    }

    public function destroy(Followup $followup): RedirectResponse
    {
        $this->authorizeAdmin();

        $this->followupService->deleteFollowup(auth()->user(), $followup);

        return redirect()->route('admin.followups.index')
            ->with('success', 'Follow-up deleted.');
    }
}
