<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeocodeLeadAddressJob implements ShouldQueue
{
    use Queueable;

    /** @var int Network-heavy external geocoders should bail early instead of hanging workers. */
    public int $timeout = 120;

    /** @var int Retry transient provider failures without spamming quotas. */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $leadId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lead = Lead::query()->find($this->leadId);
        if (! $lead || empty($lead->address) || ($lead->latitude !== null && $lead->longitude !== null)) {
            return;
        }

        // Placeholder geocoding strategy:
        // in production this should call Google Maps or Mapbox APIs.
        $hash = crc32(strtolower($lead->address));
        $lead->latitude = 20 + (($hash % 7000000) / 1000000);   // pseudo lat range
        $lead->longitude = 70 + (($hash % 9000000) / 1000000);  // pseudo lng range
        $lead->save();
    }
}
