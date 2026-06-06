<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreChecklistRequest;
use App\Http\Requests\Admin\Masters\UpdateChecklistRequest;
use App\Models\Checklist;
use App\Policies\ChecklistPolicy;
use App\Services\ChecklistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChecklistController extends Controller
{
    public function __construct(private readonly ChecklistService $service) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeChecklist('viewAny');

        $filters = ['search' => $request->string('search')->toString()];
        $records = $this->service->paginated($filters, (int) config('mowing.default_pagination', 15));

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.masters.checklists.partials.table', ['records' => $records]);
        }

        return view('admin.masters.checklists.index', [
            'records' => $records,
            'filters' => $filters,
            'routes'  => [
                'index'  => route('admin.masters.checklists.index'),
                'store'  => route('admin.masters.checklists.store'),
                'show'   => str_replace('/0', '/__ID__', route('admin.masters.checklists.show', ['checklist' => 0])),
                'update' => str_replace('/0', '/__ID__', route('admin.masters.checklists.update', ['checklist' => 0])),
            ],
        ]);
    }

    public function store(StoreChecklistRequest $request): JsonResponse
    {
        $this->authorizeChecklist('create');

        $this->service->create($request->user(), $request->validated());

        return response()->json(['message' => 'Checklist created successfully.']);
    }

    public function show(Checklist $checklist): JsonResponse
    {
        $this->authorizeChecklist('view', $checklist);

        $this->service->findWithPoints($checklist);

        return response()->json([
            'id'     => $checklist->id,
            'name'   => $checklist->name,
            'points' => $checklist->points->map(fn ($point) => [
                'id'         => $point->id,
                'heading'    => $point->heading,
                'text'       => $point->text,
                'sort_order' => $point->sort_order,
            ])->values(),
        ]);
    }

    public function update(UpdateChecklistRequest $request, Checklist $checklist): JsonResponse
    {
        $this->authorizeChecklist('update', $checklist);

        $this->service->update($request->user(), $checklist, $request->validated());

        return response()->json(['message' => 'Checklist updated successfully.']);
    }

    private function authorizeChecklist(string $ability, ?Checklist $checklist = null): void
    {
        $policy = new ChecklistPolicy();
        $user   = Auth::user();

        $allowed = match ($ability) {
            'viewAny', 'create' => $policy->{$ability}($user),
            'view', 'update'    => $checklist && $policy->{$ability}($user, $checklist),
            default             => false,
        };

        abort_unless($allowed, 403);
    }
}
