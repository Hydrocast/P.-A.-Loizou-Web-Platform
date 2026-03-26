<?php

namespace App\Mail;

use App\Models\CustomerOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sends an order confirmation email to the customer after successful checkout.
 *
 * The email is sent to the address provided at checkout, which is stored
 * on the order record. This ensures the confirmation reaches the correct
 * recipient even if the customer later changes their account email.
 *
 * The order is expected to have its items eager‑loaded before being passed
 * to this mailable, so the Blade view can access $order->items without
 * additional database queries.
 *
 * The email includes all necessary details: order number, customer contact,
 * itemised list, pricing summary, current status, pickup instructions, and
 * any stored customization metadata available on each order item.
 */
class OrderConfirmationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly CustomerOrder $order,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation #' . $this->order->order_id . ' — ' . config('business.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
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