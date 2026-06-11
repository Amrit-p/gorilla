<?php

namespace App\Notifications;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobAutoRescheduledNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->job->scheduled_date
            ? Carbon::parse($this->job->scheduled_date)->format('d M Y')
            : 'TBD';

        return (new MailMessage)
            ->subject('Job Rescheduled by System')
            ->line("Job #{$this->job->id} has been rescheduled by the system to {$date}.")
            ->action('Open Job Scheduling', route('admin.jobs.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'message' => "Job #{$this->job->id} has been rescheduled by the system to {$this->job->scheduled_date}.",
            'scheduled_date' => $this->job->scheduled_date,
        ];
    }
}
