<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreJobLevelRequest;
use App\Http\Requests\Admin\Masters\UpdateJobLevelRequest;
use App\Models\JobLevel;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class JobLevelController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::JOB_LEVELS;
    }

    protected function modelClass(): string
    {
        return JobLevel::class;
    }

    protected function title(): string
    {
        return 'Job Levels';
    }

    protected function singularLabel(): string
    {
        return 'job level';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.job-levels.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.job-levels.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.job-levels.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.job-levels.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.job-levels.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.job-levels.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.job_level';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.job-levels.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.job-levels.partials.form-fields';
    }

    public function store(StoreJobLevelRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateJobLevelRequest $request, JobLevel $jobLevel)
    {
        return $this->updateRecord($request, $jobLevel);
    }

    public function show(JobLevel $jobLevel)
    {
        $this->authorizeMaster('view');

        return response()->json($jobLevel->only([
            'id',
            'name',
            'color_code',
            'description',
            'sort_order',
            'is_active',
        ]));
    }

    public function updateStatus(\App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest $request, JobLevel $jobLevel)
    {
        return $this->toggleRecordStatus($request, $jobLevel);
    }

    public function destroy(Request $request, JobLevel $jobLevel)
    {
        return $this->destroyRecord($request, $jobLevel);
    }
}
