<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardPreferenceRequest;
use App\Services\ActivityLogService;
use App\Services\DashboardAnalyticsService;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly DashboardAnalyticsService $dashboardAnalyticsService,
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Main dashboard page with metrics and timeline.
     */
    public function index(): View
    {
        $user = request()->user();

        // Keep a simple audit entry whenever dashboard is opened.
        $this->activityLogService->log($user, 'dashboard.view', 'User opened dashboard.');

        $analytics = $this->dashboardAnalyticsService->forUser($user);

        return view('dashboard.index', [
            'analytics' => $analytics,
            'todaysJobs' => $this->dashboardService->todaysScheduledJobs(),
            'leadsByStatus' => $this->dashboardService->leadsByStatus(),
            'activityLogs' => $this->dashboardService->recentActivity(),
            'preferences' => $this->dashboardService->preferencesFor($user),
        ]);
    }

    /**
     * Save dashboard preferences with AJAX.
     */
    public function updatePreferences(DashboardPreferenceRequest $request): JsonResponse
    {
        $this->dashboardService->updatePreferences($request->user(), $request->validated());

        $this->activityLogService->log(
            $request->user(),
            'dashboard.preference_updated',
            'User updated dashboard preferences.',
            $request->validated()
        );

        return response()->json([
            'message' => 'Dashboard preferences saved.',
        ]);
    }
}
