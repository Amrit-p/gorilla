<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobDeletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Job $job,
        public User $deletedBy,
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
            'deleted_job_id' => $this->job->id,
            'deleted_by_id' => $this->deletedBy->id,
            'deleted_by_name' => $this->deletedBy->name,
            'message' => "{$this->deletedBy->name} deleted job for {$this->job->customerDisplayName()}.",
        ];
    }
}
