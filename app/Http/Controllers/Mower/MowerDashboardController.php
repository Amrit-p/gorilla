<?php

namespace App\Http\Controllers\Mower;

use App\Enums\JobOperationalPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Http\Controllers\Controller;
use App\Support\CrmConstants;
use App\Http\Requests\Mower\UpdateMowerConsumedTimeRequest;
use App\Http\Requests\Mower\UpdateMowerJobPaymentRequest;
use App\Http\Requests\Mower\UpdateMowerJobStatusRequest;
use App\Http\Requests\Mower\UploadMowerJobImagesRequest;
use App\Helpers\OptimizationHelper;
use App\Models\Job;
use App\Models\MowerRemark;
use App\Models\User;
use App\Notifications\MowerRemarkAddedNotification;
use App\Services\DashboardAnalyticsService;
use App\Support\CrmRoles;
use App\Services\JobImageManagementService;
use App\Services\MowerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MowerDashboardController extends Controller
{
    public function __construct(
        private readonly MowerDashboardService $mowerDashboardService,
        private readonly JobImageManagementService $jobImageManagementService,
        private readonly DashboardAnalyticsService $dashboardAnalyticsService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Job::class);

        $scope = $request->string('scope')->toString() ?: 'today';
        $jobs = $this->mowerDashboardService->assignedJobs($request->user(), $scope);

        if (crm_wants_partial($request)) {
            return crm_ajax_html('mower.partials.job-list', [
                'jobs' => $jobs,
                'scope' => $scope,
            ]);
        }

        return view('mower.index', [
            'jobs' => $jobs,
            'scope' => $scope,
            'listScopes' => [
                CrmConstants::MOWER_SCOPE_TODAY_SPECIAL => 'Today Special',
                CrmConstants::MOWER_SCOPE_TODAY => 'Today',
                CrmConstants::MOWER_SCOPE_UPCOMING => 'Upcoming',
                CrmConstants::MOWER_SCOPE_COMPLETED => 'Done',
                CrmConstants::MOWER_SCOPE_HOLD => 'Hold',
            ],
            'analytics' => $this->dashboardAnalyticsService->mower($request->user()),
            'workflowStatuses' => JobWorkflowStatus::values(),
            'paymentStatuses' => JobOperationalPaymentStatus::values(),
        ]);
    }

    public function show(Request $request, Job $job): View
    {
        $this->authorize('view', $job);
        $job = $this->mowerDashboardService->jobDetail($request->user(), $job);

        $images = $this->jobImageManagementService->presentAllForJob($job);

        $lastRemark = $job->client_id
            ? MowerRemark::where('client_id', $job->client_id)
                ->latest()
                ->first()
            : null;

        return view('mower.jobs.show', [
            'job' => $job,
            'beforeImages' => $images['before'],
            'afterImages' => $images['after'],
            'workflowStatuses' => JobWorkflowStatus::values(),
            'paymentStatuses' => JobOperationalPaymentStatus::values(),
            'lastRemark' => $lastRemark,
        ]);
    }

    public function updateStatus(UpdateMowerJobStatusRequest $request, Job $job): JsonResponse
    {
        $this->authorize('employeeUpdateStatus', $job);
        $job = $this->mowerDashboardService->updateStatus(
            $request->user(),
            $job,
            $request->validated('status')
        );

        return response()->json([
            'message' => 'Status updated.',
            'status' => $job->status,
        ]);
    }

    public function updatePayment(UpdateMowerJobPaymentRequest $request, Job $job): JsonResponse
    {
        $this->authorize('employeeUpdateStatus', $job);
        $job = $this->mowerDashboardService->updatePayment($request->user(), $job, $request->validated());

        return response()->json([
            'message' => 'Payment updated.',
            'payment_status' => $job->payment_status,
        ]);
    }

    public function updateConsumedTime(UpdateMowerConsumedTimeRequest $request, Job $job): JsonResponse
    {
        $this->authorize('employeeUpdateStatus', $job);
        $job = $this->mowerDashboardService->updateConsumedTime(
            $request->user(),
            $job,
            (int) $request->validated('consumed_time_minutes')
        );

        return response()->json([
            'message' => 'Time logged.',
            'consumed_time_minutes' => $job->consumed_time_minutes,
        ]);
    }

    public function uploadBefore(UploadMowerJobImagesRequest $request, Job $job): JsonResponse
    {
        $this->authorize('uploadImages', $job);
        $images = $this->mowerDashboardService->uploadBeforeImages(
            $request->user(),
            $job,
            $request->file('images', [])
        );

        return response()->json([
            'message' => 'Before photos uploaded.',
            'images' => $images,
        ]);
    }

    public function uploadAfter(UploadMowerJobImagesRequest $request, Job $job): JsonResponse
    {
        $this->authorize('uploadImages', $job);
        $images = $this->mowerDashboardService->uploadAfterImages(
            $request->user(),
            $job,
            $request->file('images', [])
        );

        return response()->json([
            'message' => 'After photos uploaded.',
            'images' => $images,
        ]);
    }

    public function deleteBefore(Request $request, Job $job, string $imageId): JsonResponse
    {
        $this->authorize('deleteJobImages', $job);
        $images = $this->mowerDashboardService->deleteBeforeImage($request->user(), $job, $imageId);

        return response()->json([
            'message' => 'Before photo removed.',
            'images' => $images,
        ]);
    }

    public function deleteAfter(Request $request, Job $job, string $imageId): JsonResponse
    {
        $this->authorize('deleteJobImages', $job);
        $images = $this->mowerDashboardService->deleteAfterImage($request->user(), $job, $imageId);

        return response()->json([
            'message' => 'After photo removed.',
            'images' => $images,
        ]);
    }

    public function storeRemark(Request $request, Job $job): JsonResponse
    {
        $this->authorize('employeeUpdateStatus', $job);

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $remark = MowerRemark::create([
            'user_id'     => $request->user()->id,
            'client_id'   => $job->client_id,
            'description' => $validated['description'],
        ]);

        $remark->load('user:id,name');

        User::query()->role(CrmRoles::OFFICE_MANAGER)->get()->each(function (User $manager) use ($job, $remark): void {
            $manager->notify(new MowerRemarkAddedNotification($job, $remark));
            OptimizationHelper::forgetNotificationUnreadCount($manager->id);
        });

        return response()->json(['message' => 'Remark saved.']);
    }
}
