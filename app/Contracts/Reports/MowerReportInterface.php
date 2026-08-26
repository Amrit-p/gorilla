<?php

namespace App\Contracts\Reports;

use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\DTOS\Response\Reports\MowerResponseReportDTO;
use App\Models\MowerPayout;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

interface MowerReportInterface
{
    /**
     * @return Collection<int, MowerResponseReportDTO>
     */
    public function generate(MowerRequestReportDTO $request): Collection;

    /**
     * @return Collection<int, MowerPayout>
     */
    public function payoutsForMower(MowerRequestReportDTO $request, int $userId): Collection;

    public function export(MowerRequestReportDTO $request): Response;
}
