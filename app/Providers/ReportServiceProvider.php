<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Reports\ChecklistReportInterface;
use App\Contracts\Reports\MowerReportInterface;
use App\Services\Reports\ChecklistReportService;
use App\Services\Reports\MowerReportService;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MowerReportInterface::class, MowerReportService::class);
        $this->app->bind(ChecklistReportInterface::class, ChecklistReportService::class);
    }

    public function boot(): void {}
}
