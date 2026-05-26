<?php

use App\Support\CrmConstants;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
