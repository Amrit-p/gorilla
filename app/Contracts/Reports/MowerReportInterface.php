<?php
namespace App\Contracts\Reports;

use App\DTOS\Request\Reports\MowerRequestReportDTO;
use App\DTOS\Response\Reports\MowerResponseReportDTO;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

interface MowerReportInterface
{
    /**
     * @param  MowerRequestReportDTO  $request
     * @return Collection<int, MowerResponseReportDTO>
     */
    public function generate(MowerRequestReportDTO $request): Collection;

    /**
     * @param  MowerRequestReportDTO  $request
     * @return Response
     */
    public function export(MowerRequestReportDTO $request): Response;

    public function updatePayout(
        int $userId,
        float $amount,
        ?string $periodStart,
        ?string $periodEnd,
        int $setBy
    ): \App\Models\MowerReportPayout;
}
