<?php

namespace App\Notifications;

use App\Models\SalaryReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalaryReceiptGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(public SalaryReceipt $receipt) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Salary Receipt Is Ready')
            ->line("Your salary receipt for {$this->receipt->period_start->format('M j, Y')} - {$this->receipt->period_end->format('M j, Y')} has been generated.")
            ->line("Amount: {$this->receipt->salary_amount}")
            ->action('View Receipt', route('admin.salary-receipts.show', $this->receipt));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'salary_receipt_id' => $this->receipt->id,
            'message' => "Your salary receipt for {$this->receipt->period_start->format('M j, Y')} - {$this->receipt->period_end->format('M j, Y')} is ready.",
        ];
    }
}
