<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait RespondsWithHtmlPartial
{
    protected function respondWithHtmlPartial(Request $request, View|string $view, array $data = []): JsonResponse|\Illuminate\View\View
    {
        if (crm_wants_partial($request)) {
            return crm_ajax_html($view, $data);
        }

        return $view instanceof View ? $view : view($view, $data);
    }
}
