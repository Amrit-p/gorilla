<?php

namespace App\Notifications;

use App\Models\Followup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpDue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Followup $followup
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
        $subject = $this->followup->subjectLabel();

        return (new MailMessage)
            ->subject("Follow-up due: {$subject}")
            ->greeting('Hello '.$notifiable->name.',')
            ->line("You have a follow-up due today for {$subject}.")
            ->line('Notes: '.$this->followup->notes)
            ->when(
                (bool) $this->followup->outcome,
                fn (MailMessage $message) => $message->line('Outcome: '.$this->followup->outcome)
            )
            ->action('View Follow-up', route('admin.followups.show', $this->followup))
            ->line('Please action this follow-up at your earliest convenience.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'followup_id' => $this->followup->id,
            'followable_type' => $this->followup->followable_type,
            'followable_id' => $this->followup->followable_id,
            'title' => $this->followup->title,
            'message' => 'Follow-up due today for '.$this->followup->subjectLabel(),
        ];
    }
}
