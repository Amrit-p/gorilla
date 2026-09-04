<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MowerClientJobCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Job $job,
        public User $createdBy,
    ) {}

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
            'created_by_id' => $this->createdBy->id,
            'created_by_name' => $this->createdBy->name,
            'message' => "{$this->createdBy->name} created a new job #{$this->job->id}.",
        ];
    }
}
