<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreRecurrenceRequest;
use App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest;
use App\Http\Requests\Admin\Masters\UpdateRecurrenceRequest;
use App\Models\Recurrence;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class RecurrenceController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::RECURRENCES;
    }

    protected function modelClass(): string
    {
        return Recurrence::class;
    }

    protected function title(): string
    {
        return 'Recurrences';
    }

    protected function singularLabel(): string
    {
        return 'recurrence';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.recurrences.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.recurrences.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.recurrences.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.recurrences.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.recurrences.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.recurrences.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.recurrence';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.recurrences.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.recurrences.partials.form-fields';
    }

    public function store(StoreRecurrenceRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateRecurrenceRequest $request, Recurrence $recurrence)
    {
        return $this->updateRecord($request, $recurrence);
    }

    public function show(Recurrence $recurrence)
    {
        return $this->showRecord($recurrence);
    }

    public function updateStatus(ToggleMasterStatusRequest $request, Recurrence $recurrence)
    {
        return $this->toggleRecordStatus($request, $recurrence);
    }

    public function destroy(Request $request, Recurrence $recurrence)
    {
        return $this->destroyRecord($request, $recurrence);
    }
}
