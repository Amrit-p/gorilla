<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadConvertedToClient
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Lead $lead,
        public User $actor
    ) {}
}
