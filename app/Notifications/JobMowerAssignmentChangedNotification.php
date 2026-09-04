<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobMowerAssignmentChangedNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'message' => "Mower assignment changed for Job #{$this->job->id}.",
        ];
    }
}
