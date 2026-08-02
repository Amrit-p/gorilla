<?php

namespace App\Listeners;

use App\Events\LeadConvertedToClient;
use App\Services\LeadConversionService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleLeadConvertedToClient implements ShouldQueue
{
    public function __construct(
        private readonly LeadConversionService $leadConversionService
    ) {}

    public function handle(LeadConvertedToClient $event): void
    {
        $lead = $event->lead->fresh();
        if (! $lead || $lead->converted_at === null) {
            return;
        }

        $this->leadConversionService->notifyManagers($lead);
    }
}
