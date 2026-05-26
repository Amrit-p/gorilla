<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreEquipmentTypeRequest;
use App\Http\Requests\Admin\Masters\UpdateEquipmentTypeRequest;
use App\Models\EquipmentType;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class EquipmentTypeController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::EQUIPMENT_TYPES;
    }

    protected function modelClass(): string
    {
        return EquipmentType::class;
    }

    protected function title(): string
    {
        return 'Equipment Types';
    }

    protected function singularLabel(): string
    {
        return 'equipment type';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.equipment-types.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.equipment-types.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.equipment-types.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.equipment-types.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.equipment-types.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.equipment-types.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.equipment_type';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.equipment-types.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.equipment-types.partials.form-fields';
    }

    public function store(StoreEquipmentTypeRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateEquipmentTypeRequest $request, EquipmentType $equipmentType)
    {
        return $this->updateRecord($request, $equipmentType);
    }

    public function show(EquipmentType $equipmentType)
    {
        return $this->showRecord($equipmentType);
    }

    public function updateStatus(\App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest $request, EquipmentType $equipmentType)
    {
        return $this->toggleRecordStatus($request, $equipmentType);
    }

    public function destroy(Request $request, EquipmentType $equipmentType)
    {
        return $this->destroyRecord($request, $equipmentType);
    }
}
