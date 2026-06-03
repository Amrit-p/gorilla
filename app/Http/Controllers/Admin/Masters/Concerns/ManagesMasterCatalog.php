<?php

namespace App\Http\Controllers\Admin\Masters\Concerns;

use App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest;
use App\Policies\MasterCatalogPolicy;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait ManagesMasterCatalog
{
    protected MasterCatalogService $masterCatalogService;

    abstract protected function catalogKey(): string;

    /** @return class-string<Model> */
    abstract protected function modelClass(): string;

    abstract protected function title(): string;

    abstract protected function singularLabel(): string;

    abstract protected function indexRoute(): string;

    abstract protected function storeRoute(): string;

    abstract protected function showRouteName(): string;

    abstract protected function updateRouteName(): string;

    abstract protected function statusRouteName(): string;

    abstract protected function destroyRouteName(): string;

    abstract protected function logPrefix(): string;

    abstract protected function tablePartial(): string;

    abstract protected function formFieldsPartial(): string;

    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeMaster('viewAny');

        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->get('status', ''),
        ];

        $records = $this->masterCatalogService->paginated(
            $this->catalogKey(),
            $this->modelClass(),
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html($this->tablePartial(), ['records' => $records]);
        }

        $routeParam = match ($this->catalogKey()) {
            MasterCatalog::SERVICE_TYPES => ['serviceType' => 0],
            MasterCatalog::EQUIPMENT_TYPES => ['equipmentType' => 0],
            MasterCatalog::SAFETY_TYPES => ['safetyType' => 0],
            MasterCatalog::ZONES => ['zone' => 0],
            MasterCatalog::RECURRENCES => ['recurrence' => 0],
            MasterCatalog::ACCOUNTING_LEVELS => ['accountingLevel' => 0],
            MasterCatalog::JOB_LEVELS => ['jobLevel' => 0],
            default => ['record' => 0],
        };

        $placeholder = fn (string $name): string => str_replace('/0', '/__ID__', route($name, $routeParam));

        return view('admin.masters.layout.index', [
            'title' => $this->title(),
            'singularLabel' => $this->singularLabel(),
            'records' => $records,
            'filters' => $filters,
            'tablePartial' => $this->tablePartial(),
            'formFieldsPartial' => $this->formFieldsPartial(),
            'showColorCode' => $this->catalogKey() === MasterCatalog::EQUIPMENT_TYPES,
            'routes' => [
                'index' => route($this->indexRoute()),
                'store' => route($this->storeRoute()),
                'show' => $placeholder($this->showRouteName()),
                'update' => $placeholder($this->updateRouteName()),
                'status' => $placeholder($this->statusRouteName()),
                'destroy' => $placeholder($this->destroyRouteName()),
            ],
        ]);
    }

    protected function storeRecord(FormRequest $request): JsonResponse
    {
        $this->authorizeMaster('create');

        $this->masterCatalogService->create(
            $request->user(),
            $this->catalogKey(),
            $this->modelClass(),
            $request->validated(),
            $this->logPrefix().'.created'
        );

        return response()->json(['message' => ucfirst($this->singularLabel()).' created successfully.']);
    }

    protected function updateRecord(FormRequest $request, Model $record): JsonResponse
    {
        $this->authorizeMaster('update', $record);

        $this->masterCatalogService->update(
            $request->user(),
            $this->catalogKey(),
            $record,
            $request->validated(),
            $this->logPrefix().'.updated'
        );

        return response()->json(['message' => ucfirst($this->singularLabel()).' updated successfully.']);
    }

    protected function showRecord(Model $record): JsonResponse
    {
        $this->authorizeMaster('view', $record);

        return response()->json($record->only([
            'id',
            'name',
            'color_code',
            'sort_order',
            'is_active',
        ]));
    }

    protected function toggleRecordStatus(ToggleMasterStatusRequest $request, Model $record): JsonResponse
    {
        $this->authorizeMaster('update', $record);

        $this->masterCatalogService->toggleStatus(
            $request->user(),
            $this->catalogKey(),
            $record,
            (bool) $request->validated('is_active'),
            $this->logPrefix().'.status_updated'
        );

        return response()->json(['message' => 'Status updated successfully.']);
    }

    protected function destroyRecord(Request $request, Model $record): JsonResponse
    {
        $this->authorizeMaster('delete', $record);

        $this->masterCatalogService->delete(
            $request->user(),
            $this->catalogKey(),
            $record,
            $this->logPrefix().'.deleted'
        );

        return response()->json(['message' => ucfirst($this->singularLabel()).' deleted successfully.']);
    }

    protected function authorizeMaster(string $ability, ?Model $record = null): void
    {
        $policy = new MasterCatalogPolicy;

        $user = auth()->user();
        $allowed = match ($ability) {
            'viewAny', 'create' => $policy->{$ability}($user),
            'view' => $policy->viewAny($user),
            'update', 'delete' => $record ? $policy->{$ability}($user, $record) : false,
            default => false,
        };

        abort_unless($allowed, 403);
    }
}
