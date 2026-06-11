<?php

use App\Enums\JobWorkflowStatus;
use App\Models\Job;
use App\Support\CrmConstants;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

if (! function_exists('crm_pagination')) {
    function crm_pagination(): int
    {
        return CrmConstants::defaultPagination();
    }
}

if (! function_exists('crm_ajax_html')) {
    /**
     * Standard AJAX list response for Gorilla CRM index pages.
     */
    function crm_ajax_html(View|string $view, array $data = [], int $status = 200): JsonResponse
    {
        $html = $view instanceof View ? $view->with($data)->render() : view($view, $data)->render();

        return response()->json(['html' => $html], $status);
    }
}

if (! function_exists('crm_wants_partial')) {
    function crm_wants_partial(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson();
    }
}

if (! function_exists('job_row_color_class')) {
    /**
     * Background color class for a job row based on its schedule date and status.
     *
     * - Completed jobs are green.
     * - Held jobs or jobs scheduled in the past are light red.
     * - Pending jobs scheduled for today are orange.
     */
    function job_row_color_class(Job $job): string
    {
        if ($job->status === JobWorkflowStatus::COMPLETED->value) {
            return 'bg-green-50';
        }

        $scheduledDate = $job->scheduled_date;

        if ($job->status === JobWorkflowStatus::HOLD->value
            || ($scheduledDate !== null && $scheduledDate->lt(Carbon::today()))) {
            return 'bg-red-50';
        }

        if ($scheduledDate !== null
            && $scheduledDate->isToday()
            && $job->status === JobWorkflowStatus::PENDING->value) {
            return 'bg-orange-50';
        }

        return '';
    }
}
