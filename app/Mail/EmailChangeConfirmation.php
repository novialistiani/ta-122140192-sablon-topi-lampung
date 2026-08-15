<?php

namespace App\Mail;

use App\Models\EmailChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $request;
    public $confirmUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailChangeRequest $request)
    {
        $this->request = $request;
        $this->confirmUrl = route('profile.confirm-email-change', ['token' => $request->token]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Perubahan Email - LGI STORE',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-confirmation',
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
