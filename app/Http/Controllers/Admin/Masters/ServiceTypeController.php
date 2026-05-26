<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreServiceTypeRequest;
use App\Http\Requests\Admin\Masters\UpdateServiceTypeRequest;
use App\Models\ServiceType;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::SERVICE_TYPES;
    }

    protected function modelClass(): string
    {
        return ServiceType::class;
    }

    protected function title(): string
    {
        return 'Service Types';
    }

    protected function singularLabel(): string
    {
        return 'service type';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.service-types.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.service-types.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.service-types.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.service-types.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.service-types.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.service-types.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.service_type';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.service-types.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.service-types.partials.form-fields';
    }

    public function store(StoreServiceTypeRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType)
    {
        return $this->updateRecord($request, $serviceType);
    }

    public function show(ServiceType $serviceType)
    {
        return $this->showRecord($serviceType);
    }

    public function updateStatus(\App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest $request, ServiceType $serviceType)
    {
        return $this->toggleRecordStatus($request, $serviceType);
    }

    public function destroy(Request $request, ServiceType $serviceType)
    {
        return $this->destroyRecord($request, $serviceType);
    }
}
