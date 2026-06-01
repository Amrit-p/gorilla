<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Reports\MowerReportInterface;
use App\Services\Reports\MowerReportService;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MowerReportInterface::class, MowerReportService::class);
    }

    public function boot(): void
    {
    }
}
