<?php

namespace App\Notifications;

use App\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SupportTicketCreated extends Notification
{
    use Queueable;

    public $ticket;
    protected $replayBy;

    /**
     * Create a new notification instance.
     * @param SupportTicket $ticket
     * @param $replayBy;
     */
    public function __construct(SupportTicket $ticket, $replayBy)
    {
        $this->ticket = $ticket;
        $this->replayBy = $replayBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url("/support/tickets/{$this->ticket->id}/edit");
        $subject = $this->replayBy
            ? "New message with ticket number #{$this->ticket->id} replied"
            : "New ticket number #{$this->ticket->id} created";

        if ($this->replayBy) {
            $line = $this->replayBy->isCustomer()
                ? "Your customer has replied to your message with subject: {$this->ticket->subject}"
                : "We have replied to your message please reply to the message we sent";
        } else {
            $line = "New ticket has been created with the number #{$this->ticket->id} and a subject: {$this->ticket->subject}";
        }


        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello!')
            ->line($line)
            ->action('View Ticket', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
