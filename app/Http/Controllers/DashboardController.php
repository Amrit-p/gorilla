<?php

namespace App\Http\Controllers;

use App\Enums\JobWorkflowStatus;
use App\Http\Requests\DashboardPreferenceRequest;
use App\Models\Job;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\DashboardAnalyticsService;
use App\Services\DashboardService;
use App\Support\CrmRoles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $dashboardType = $analytics['type'] ?? 'admin';
        $isAdmin = $dashboardType === 'admin';

        return view('dashboard.index', [
            'analytics' => $analytics,
            'todaysJobs' => $this->dashboardService->todaysScheduledJobs(),
            'leadsByStatus' => $this->dashboardService->leadsByStatus(),
            'activityLogs' => $this->dashboardService->recentActivity(),
            'preferences' => $this->dashboardService->preferencesFor($user),
            'threeWeekSchedule' => $isAdmin ? $this->dashboardService->threeWeekScheduleSummary() : [],
            'employees' => $isAdmin
                ? User::query()->role(CrmRoles::MOWER)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'efficiency'])
                : collect(),
            'workflowStatuses' => $isAdmin ? JobWorkflowStatus::values() : [],
        ]);
    }

    /**
     * AJAX: server-rendered job table for a given date (used by the 3-week schedule panel).
     * Returns HTML so the full admin table partial—including all action buttons—is reused.
     */
    public function dailyJobsTable(Request $request): Response
    {
        $request->validate(['date' => 'required|date']);

        $jobs = Job::query()
            ->with([
                'client:id,name,customer_unique_id',
                'zone:id,name',
                'equipmentType:id,name,color_code',
                'jobLevel:id,name,color_code',
                'doneByUser:id,name',
                'assignedEmployees:id,name',
                'recurrence:id,name',
            ])
            ->whereDate('scheduled_date', $request->date)
            ->whereNotIn('status', ['Cancelled'])
            ->orderBy('numeric_priority')
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->paginate(50);

        return response(view('dashboard.partials.daily-jobs-table', compact('jobs')));
    }

    /**
     * AJAX: re-render the three-week calendar grid after a job action.
     */
    public function threeWeekGrid(): Response
    {
        $weeks = $this->dashboardService->threeWeekScheduleSummary();

        return response(view('dashboard.partials.three-week-grid', compact('weeks')));
    }

    /**
     * AJAX: filtered revenue & job-performance chart data for the analytics sidebar.
     */
    public function analyticsCharts(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $start = $request->input('start_date');
        $end = $request->input('end_date');

        return response()->json([
            'revenue' => $this->dashboardAnalyticsService->revenueChartData($start, $end),
            'jobs' => $this->dashboardAnalyticsService->jobPerformanceChartData($start, $end),
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
