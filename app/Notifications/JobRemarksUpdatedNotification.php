<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobRemarksUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Job Remarks Updated')
            ->line("Remarks for Job #{$this->job->id} have been updated.")
            ->action('Open Mobile Dashboard', route('employee.mobile.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'job_id'  => $this->job->id,
            'message' => "Remarks updated for Job #{$this->job->id}.",
        ];
    }
}
