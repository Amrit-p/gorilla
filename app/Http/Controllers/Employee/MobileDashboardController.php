<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\UpdateEmployeeJobStatusRequest;
use App\Models\Job;
use App\Services\EmployeeMobileDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileDashboardController extends Controller
{
    public function __construct(
        private readonly EmployeeMobileDashboardService $employeeMobileDashboardService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Job::class);

        $employee = $request->user();

        return view('employee.mobile.index', [
            'todaysJobs' => $this->employeeMobileDashboardService->todaysJobs($employee),
            'completedLedger' => $this->employeeMobileDashboardService->completedLedger($employee),
        ]);
    }

    public function updateStatus(UpdateEmployeeJobStatusRequest $request, Job $job): JsonResponse
    {
        $this->authorize('employeeUpdateStatus', $job);
        $this->employeeMobileDashboardService->updateJobStatus(
            $request->user(),
            $job,
            $request->validated('status')
        );

        return response()->json([
            'message' => 'Job status updated successfully.',
        ]);
    }
}
