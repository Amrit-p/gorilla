<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreAccountingLevelRequest;
use App\Http\Requests\Admin\Masters\UpdateAccountingLevelRequest;
use App\Models\AccountingLevel;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class AccountingLevelController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::ACCOUNTING_LEVELS;
    }

    protected function modelClass(): string
    {
        return AccountingLevel::class;
    }

    protected function title(): string
    {
        return 'Accounting Levels';
    }

    protected function singularLabel(): string
    {
        return 'accounting level';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.accounting-levels.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.accounting-levels.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.accounting-levels.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.accounting-levels.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.accounting-levels.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.accounting-levels.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.accounting_level';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.accounting-levels.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.accounting-levels.partials.form-fields';
    }

    public function store(StoreAccountingLevelRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateAccountingLevelRequest $request, AccountingLevel $accountingLevel)
    {
        return $this->updateRecord($request, $accountingLevel);
    }

    public function show(AccountingLevel $accountingLevel)
    {
        $this->authorizeMaster('view');

        return response()->json($accountingLevel->only([
            'id',
            'name',
            'description',
            'sort_order',
            'is_active',
        ]));
    }

    public function updateStatus(\App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest $request, AccountingLevel $accountingLevel)
    {
        return $this->toggleRecordStatus($request, $accountingLevel);
    }

    public function destroy(Request $request, AccountingLevel $accountingLevel)
    {
        return $this->destroyRecord($request, $accountingLevel);
    }
}
