<?php

namespace Tests\Integration\Mail;

use PHPUnit\Framework\Attributes\Test;
use App\Mail\OrderConfirmationEmail;
use App\Models\CustomerOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for the OrderConfirmationEmail mailable.
 *
 * Covers constructor property storage, queue implementation,
 * envelope configuration, content view, and attachments.
 */
class OrderConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Constructor property storage
    // -------------------------------------------------------------------------

    #[Test]
    /** Constructor stores the order on the mailable. */
    public function constructor_stores_order(): void
    {
        $order = CustomerOrder::factory()->create();
        $mailable = new OrderConfirmationEmail($order);

        $this->assertSame($order->order_id, $mailable->order->order_id);
    }

    // -------------------------------------------------------------------------
    // Queue
    // -------------------------------------------------------------------------

    #[Test]
    /** Mailable implements ShouldQueue. */
    public function mailable_implements_should_queue(): void
    {
        $order = CustomerOrder::factory()->create();
        $mailable = new OrderConfirmationEmail($order);

        $this->assertInstanceOf(ShouldQueue::class, $mailable);
    }

    // -------------------------------------------------------------------------
    // Envelope
    // -------------------------------------------------------------------------

    #[Test]
    /** Envelope subject contains order ID and business name. */
    public function envelope_subject_contains_order_id_and_business_name(): void
    {
        config(['business.name' => 'Test Business']);
        $order = CustomerOrder::factory()->create();
        $mailable = new OrderConfirmationEmail($order);

        $this->assertSame(
            'Order Confirmation #' . $order->order_id . ' — Test Business',
            $mailable->envelope()->subject,
        );
    }

    // -------------------------------------------------------------------------
    // Content
    // -------------------------------------------------------------------------

    #[Test]
    /** Content view is emails.orders.confirmation. */
    public function content_view_is_orders_confirmation(): void
    {
        $order = CustomerOrder::factory()->create();
        $mailable = new OrderConfirmationEmail($order);

        $this->assertSame(
            'emails.orders.confirmation',
            $mailable->content()->view,
        );
    }

    // -------------------------------------------------------------------------
    // Attachments
    // -------------------------------------------------------------------------

    #[Test]
    /** Attachments returns an empty array. */
    public function attachments_returns_empty_array(): void
    {
        $order = CustomerOrder::factory()->create();
        $mailable = new OrderConfirmationEmail($order);

        $this->assertSame([], $mailable->attachments());
    }
}