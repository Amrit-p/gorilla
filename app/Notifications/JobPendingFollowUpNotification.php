<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobPendingFollowUpNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysPending = (int) now()->diffInDays($this->job->updated_at);

        return (new MailMessage)
            ->subject('Pending Job Follow-Up Reminder')
            ->line("Job #{$this->job->id} has been pending for {$daysPending} days with no updates.")
            ->line('Please review and take the necessary action.')
            ->action('Open Job Scheduling', route('admin.jobs.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'job_id'  => $this->job->id,
            'message' => "Follow-up required: Job #{$this->job->id} has been pending with no updates for 2+ days.",
        ];
    }
}
