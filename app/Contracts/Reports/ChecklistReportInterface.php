<?php

namespace App\Contracts\Reports;

use App\DTOS\Request\Reports\ChecklistReportRequestDTO;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

interface ChecklistReportInterface
{
    public function generate(ChecklistReportRequestDTO $request): Collection;

    public function export(ChecklistReportRequestDTO $request): Response;
}
