<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $orderType;
    public $customerName;
    public $paymentDeadline;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $orderType, $customerName, $paymentDeadline)
    {
        $this->order = $order;
        $this->orderType = $orderType;
        $this->customerName = $customerName;
        $this->paymentDeadline = $paymentDeadline;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan Anda Telah Disetujui - LGI Store',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        try {
            Log::info('Preparing order approved email', [
                'order_id' => $this->order->id,
                'order_type' => $this->orderType,
                'customer_name' => $this->customerName,
                'payment_deadline' => $this->paymentDeadline
            ]);

            return new Content(
                view: 'emails.order-approved',
            );
        } catch (\Exception $e) {
            Log::error('Error preparing order approved email', [
                'error' => $e->getMessage(),
                'order_id' => $this->order->id ?? null
            ]);
            throw $e;
        }
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
