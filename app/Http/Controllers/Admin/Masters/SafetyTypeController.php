<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreSafetyTypeRequest;
use App\Http\Requests\Admin\Masters\UpdateSafetyTypeRequest;
use App\Models\SafetyType;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class SafetyTypeController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::SAFETY_TYPES;
    }

    protected function modelClass(): string
    {
        return SafetyType::class;
    }

    protected function title(): string
    {
        return 'Safety Types';
    }

    protected function singularLabel(): string
    {
        return 'safety type';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.safety-types.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.safety-types.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.safety-types.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.safety-types.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.safety-types.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.safety-types.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.safety_type';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.safety-types.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.safety-types.partials.form-fields';
    }

    public function store(StoreSafetyTypeRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateSafetyTypeRequest $request, SafetyType $safetyType)
    {
        return $this->updateRecord($request, $safetyType);
    }

    public function show(SafetyType $safetyType)
    {
        return $this->showRecord($safetyType);
    }

    public function updateStatus(\App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest $request, SafetyType $safetyType)
    {
        return $this->toggleRecordStatus($request, $safetyType);
    }

    public function destroy(Request $request, SafetyType $safetyType)
    {
        return $this->destroyRecord($request, $safetyType);
    }
}
