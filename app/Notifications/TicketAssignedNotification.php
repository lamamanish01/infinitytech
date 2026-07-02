<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // Use 'database' and optionally 'mail' or 'broadcast'
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ticket Assigned: ' . $this->ticket->ticket_no)
            ->line('A new ticket has been assigned to you.')
            ->line('Ticket #: ' . $this->ticket->ticket_no)
            ->line('Subject: ' . $this->ticket->subject)
            ->action('View Ticket', route('ticket.show', $this->ticket->id))
            ->line('Thank you for using our support system.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_no' => $this->ticket->ticket_no,
            'subject'   => $this->ticket->subject,
            'assigned_by' => auth()->id(),
            'message'   => 'Ticket ' . $this->ticket->ticket_no . ' has been assigned to you.',
        ];
    }
}
