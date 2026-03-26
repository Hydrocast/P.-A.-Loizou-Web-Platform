<?php

namespace Tests\Integration\Mail;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\OrderStatus;
use App\Mail\OrderStatusNotificationEmail;
use App\Models\CustomerOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for the OrderStatusNotificationEmail mailable.
 *
 * Covers constructor property storage, queue implementation,
 * envelope configuration, content view, and attachments.
 */
class OrderStatusNotificationEmailTest extends TestCase
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
        $mailable = new OrderStatusNotificationEmail($order);

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
        $mailable = new OrderStatusNotificationEmail($order);

        $this->assertInstanceOf(ShouldQueue::class, $mailable);
    }

    // -------------------------------------------------------------------------
    // Envelope
    // -------------------------------------------------------------------------

    #[Test]
    /** Envelope subject contains order ID, status label, and business name. */
    public function envelope_subject_contains_order_id_status_label_and_business_name(): void
    {
        config(['business.name' => 'Test Business']);
        $order = CustomerOrder::factory()->processing()->create();
        $mailable = new OrderStatusNotificationEmail($order);

        $this->assertSame(
            'Your Order #' . $order->order_id . ' is now Processing — Test Business',
            $mailable->envelope()->subject,
        );
    }

    // -------------------------------------------------------------------------
    // Content
    // -------------------------------------------------------------------------

    #[Test]
    /** Content view is emails.orders.status-notification. */
    public function content_view_is_orders_status_notification(): void
    {
        $order = CustomerOrder::factory()->create();
        $mailable = new OrderStatusNotificationEmail($order);

        $this->assertSame(
            'emails.orders.status-notification',
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
        $mailable = new OrderStatusNotificationEmail($order);

        $this->assertSame([], $mailable->attachments());
    }
}