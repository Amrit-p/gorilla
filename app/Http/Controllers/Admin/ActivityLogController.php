<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterActivityLogRequest;
use App\Services\ActivityLogModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogModuleService $activityLogModuleService
    ) {}

    public function index(FilterActivityLogRequest $request): View|JsonResponse
    {
        $filters = $request->validated();

        $logs = $this->activityLogModuleService->paginatedLogs(
            $filters,
            (int) config('mowing.default_pagination', 20)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.activity-logs.partials.table', compact('logs'));
        }

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => $this->activityLogModuleService->actions(),
            'actors' => $this->activityLogModuleService->actors(),
        ]);
    }
}
