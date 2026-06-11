<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job, public User $verifier) {}

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
            ->subject('Job Verified')
            ->line("Job #{$this->job->id} was verified by {$this->verifier->name}.")
            ->action('Open Job Scheduling', route('admin.jobs.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'message' => "Job #{$this->job->id} was verified by {$this->verifier->name}.",
            'verified_by' => $this->verifier->name,
        ];
    }
}
