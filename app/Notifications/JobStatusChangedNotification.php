<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobStatusChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Job $job) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->job->customerDisplayName();

        return (new MailMessage)
            ->subject('Job Marked as Completed')
            ->line("The job for {$client} has been marked as Completed.")
            ->action('Open Job Scheduling', route('admin.jobs.index'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $client = $this->job->customerDisplayName();

        return [
            'job_id' => $this->job->id,
            'message' => "Job for {$client} has been marked as Completed.",
            'status' => $this->job->status,
        ];
    }
}
