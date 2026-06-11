<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithHtmlPartial;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use RespondsWithHtmlPartial;
}
