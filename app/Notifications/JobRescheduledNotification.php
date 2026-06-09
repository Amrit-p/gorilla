<?php

namespace App\Notifications;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobRescheduledNotification extends Notification
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

        $time = $this->job->scheduled_time
            ? ' at ' . Carbon::parse($this->job->scheduled_time)->format('h:i A')
            : '';

        return (new MailMessage)
            ->subject('Job Rescheduled')
            ->line("Job #{$this->job->id} has been rescheduled to {$date}{$time}.")
            ->action('Open Job Scheduling', route('admin.jobs.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'job_id'         => $this->job->id,
            'message'        => "Job #{$this->job->id} rescheduled to {$this->job->scheduled_date}.",
            'scheduled_date' => $this->job->scheduled_date,
            'scheduled_time' => $this->job->scheduled_time,
        ];
    }
}
