<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Job Created by Mower')
            ->line("{$this->createdBy->name} created a new job #{$this->job->id}.")
            ->line('Address: '.($this->job->client_address ?? 'N/A'))
            ->line('Scheduled: '.($this->job->scheduled_date ?? 'TBD'))
            ->action('View Job', route('admin.jobs.show', $this->job));
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
