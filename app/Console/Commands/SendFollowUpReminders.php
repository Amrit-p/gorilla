<?php

namespace App\Console\Commands;

use App\Notifications\FollowUpDue;
use App\Repositories\FollowupRepository;
use Illuminate\Console\Command;

class SendFollowUpReminders extends Command
{
    protected $signature = 'followups:notify';

    protected $description = 'Send reminders for follow-ups due today';

    public function handle(FollowupRepository $repository): int
    {
        $followups = $repository->dueTodayPending();

        $count = 0;

        foreach ($followups as $followup) {
            $recipient = $followup->assignedTo ?? $followup->createdBy;

            if (! $recipient) {
                continue;
            }

            $recipient->notify(new FollowUpDue($followup));
            $count++;
        }

        $this->info("Sent {$count} follow-up reminder(s).");

        return self::SUCCESS;
    }
}
