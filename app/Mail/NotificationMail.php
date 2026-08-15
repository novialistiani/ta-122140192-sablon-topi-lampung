<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notification $notification;
    public array $data;
    public string $templateView;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification, array $data, string $templateView)
    {
        $this->notification = $notification;
        $this->data = $data;
        $this->templateView = $templateView;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->templateView,
            with: [
                'notification' => $this->notification,
                'data' => $this->data,
                'title' => $this->notification->title,
                'message' => $this->notification->message,
                'actionUrl' => $this->notification->action_url,
                'actionText' => $this->notification->action_text,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
