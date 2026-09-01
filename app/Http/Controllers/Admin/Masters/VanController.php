<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreVanRequest;
use App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest;
use App\Http\Requests\Admin\Masters\UpdateVanRequest;
use App\Models\Van;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class VanController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::VANS;
    }

    protected function modelClass(): string
    {
        return Van::class;
    }

    protected function title(): string
    {
        return 'Vans';
    }

    protected function singularLabel(): string
    {
        return 'van';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.vans.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.vans.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.vans.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.vans.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.vans.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.vans.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.van';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.vans.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.vans.partials.form-fields';
    }

    public function store(StoreVanRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateVanRequest $request, Van $van)
    {
        return $this->updateRecord($request, $van);
    }

    public function show(Van $van)
    {
        return $this->showRecord($van);
    }

    public function updateStatus(ToggleMasterStatusRequest $request, Van $van)
    {
        return $this->toggleRecordStatus($request, $van);
    }

    public function destroy(Request $request, Van $van)
    {
        return $this->destroyRecord($request, $van);
    }
}
