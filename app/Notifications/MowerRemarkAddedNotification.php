<?php

namespace App\Notifications;

use App\Models\Job;
use App\Models\MowerRemark;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MowerRemarkAddedNotification extends Notification
{
    use Queueable;

    public function __construct(public Job $job, public MowerRemark $remark) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Mower Remark Added')
            ->line("A remark was added to Job #{$this->job->id} by {$this->remark->user?->name}.")
            ->line("\"{$this->remark->description}\"")
            ->action('Open Job', route('admin.jobs.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'job_id'  => $this->job->id,
            'message' => "New remark on Job #{$this->job->id}: \"{$this->remark->description}\"",
        ];
    }
}
