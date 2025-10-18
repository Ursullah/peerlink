<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanFundedNotification extends Notification
{
    use Queueable;

    protected $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $borrowerName = $this->loan->borrower->name;
        $amount = number_format($this->loan->principal_amount / 100, 2);

        return (new MailMessage)
            ->subject('Congratulations! Your Loan Has Been Funded')
            ->greeting("Hello, {$borrowerName}!")
            ->line("We have great news! Your loan request for KES {$amount} has been successfully funded by a lender.")
            ->line('The funds are now available in your PeerLink wallet.')
            ->action('View My Dashboard', url('/dashboard'))
            ->line('Thank you for using PeerLink!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
