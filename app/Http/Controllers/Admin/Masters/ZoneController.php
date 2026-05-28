<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreZoneRequest;
use App\Http\Requests\Admin\Masters\UpdateZoneRequest;
use App\Models\Zone;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::ZONES;
    }

    protected function modelClass(): string
    {
        return Zone::class;
    }

    protected function title(): string
    {
        return 'Zones';
    }

    protected function singularLabel(): string
    {
        return 'zone';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.zones.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.zones.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.zones.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.zones.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.zones.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.zones.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.zone';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.zones.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.zones.partials.form-fields';
    }

    public function store(StoreZoneRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        return $this->updateRecord($request, $zone);
    }

    public function show(Zone $zone)
    {
        return $this->showRecord($zone);
    }

    public function updateStatus(\App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest $request, Zone $zone)
    {
        return $this->toggleRecordStatus($request, $zone);
    }

    public function destroy(Request $request, Zone $zone)
    {
        return $this->destroyRecord($request, $zone);
    }
}
