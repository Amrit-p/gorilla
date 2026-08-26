<?php

namespace App\Contracts\Reports;

use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\DTOS\Response\Reports\MowerResponseReportDTO;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

interface MowerReportInterface
{
    /**
     * @return Collection<int, MowerResponseReportDTO>
     */
    public function generate(MowerRequestReportDTO $request): Collection;

    public function export(MowerRequestReportDTO $request): Response;
}
