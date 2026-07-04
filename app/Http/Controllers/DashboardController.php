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
use App\Support\QueryFilters\JobListFilter;
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
        $showSchedule = $isAdmin || $dashboardType === 'sales';

        return view('dashboard.index', [
            'analytics' => $analytics,
            'todaysJobs' => $this->dashboardService->todaysScheduledJobs(),
            'leadsByStatus' => $this->dashboardService->leadsByStatus(),
            'activityLogs' => $this->dashboardService->recentActivity(),
            'preferences' => $this->dashboardService->preferencesFor($user),
            'threeWeekSchedule' => $showSchedule ? $this->dashboardService->threeWeekScheduleSummary() : [],
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
    public function dailyJobsTable(Request $request, JobListFilter $jobListFilter): Response
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'date_range' => 'nullable|array',
            'date_range.start' => 'nullable|date',
            'date_range.end' => 'nullable|date',
            'zone_id' => 'nullable|integer|exists:zones,id',
            'worker_id' => 'nullable|integer|exists:users,id',
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string',
            'recurrence_id' => 'nullable|integer|exists:recurrences,id',
            'assignment' => 'nullable|in:assigned,unassigned',
            'payment_mode' => 'nullable|string',
            'payment_status' => 'nullable|string',
            'equipment_type_id' => 'nullable|integer',
            'job_level_id' => 'nullable|integer|exists:job_levels,id',
            'customer_type' => 'nullable|string',
            'service_type' => 'nullable|string',
        ]);

        $query = Job::query()
            ->with([
                'client:id,name,customer_unique_id',
                'zone:id,name',
                'equipmentType:id,name,color_code',
                'jobLevel:id,name,color_code',
                'doneByUser:id,name',
                'assignedEmployees:id,name',
                'recurrence:id,name',
            ])
            ->when(($validated['status'] ?? null) !== 'Cancelled', fn ($q) => $q->whereNotIn('status', ['Cancelled']));

        $jobListFilter->apply($query, [
            'search' => $validated['search'] ?? null,
            'zone_id' => $validated['zone_id'] ?? null,
            'done_by_user_id' => $validated['worker_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'recurrence_id' => $validated['recurrence_id'] ?? null,
            'assignment' => $validated['assignment'] ?? null,
            'payment_mode' => $validated['payment_mode'] ?? null,
            'payment_status' => $validated['payment_status'] ?? null,
            'equipment_type_id' => $validated['equipment_type_id'] ?? null,
            'job_level_id' => $validated['job_level_id'] ?? null,
            'customer_type' => $validated['customer_type'] ?? null,
            'service_type' => $validated['service_type'] ?? null,
            // The date range picker defaults to the clicked day, but the user
            // may widen it from within the panel; fall back to `date` when absent.
            'date_range_start' => $validated['date_range']['start'] ?? $validated['date'],
            'date_range_end' => $validated['date_range']['end'] ?? $validated['date'],
        ]);

        $jobs = $query
            ->orderBy('numeric_priority')
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->paginate(50);

        return response(view('dashboard.partials.daily-jobs-table', compact('jobs')));
    }

    /**
     * AJAX: re-render the three-week calendar grid (optionally filtered).
     */
    public function threeWeekGrid(Request $request): Response
    {
        $request->validate([
            'zone_id' => 'nullable|integer|exists:zones,id',
            'worker_id' => 'nullable|integer|exists:users,id',
            'search' => 'nullable|string|max:100',
            'week_offset' => 'nullable|integer|min:-52|max:52',
        ]);

        $weeks = $this->dashboardService->threeWeekScheduleSummary([
            'zone_id' => $request->integer('zone_id') ?: null,
            'worker_id' => $request->integer('worker_id') ?: null,
            'search' => $request->string('search')->toString() ?: null,
            'week_offset' => $request->integer('week_offset'),
        ]);

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
     * AJAX: re-render the mower performance table for a given date range.
     */
    public function mowerPerformanceTable(Request $request): Response
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $mowerPerformance = $this->dashboardAnalyticsService->mowerPerformanceData(
            $request->string('start_date')->toString(),
            $request->string('end_date')->toString()
        );

        return response(view('dashboard.partials.mower-performance-table-rows', compact('mowerPerformance')));
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
