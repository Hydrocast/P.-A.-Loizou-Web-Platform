<?php

namespace Tests\Integration\Mail;

use PHPUnit\Framework\Attributes\Test;
use App\Mail\WelcomeEmail;
use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for the WelcomeEmail mailable.
 *
 * Covers constructor property storage, queue implementation,
 * envelope configuration, content view, and attachments.
 */
class WelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Constructor property storage
    // -------------------------------------------------------------------------

    #[Test]
    /** Constructor stores the customer on the mailable. */
    public function constructor_stores_customer(): void
    {
        $customer = Customer::factory()->create();
        $mailable = new WelcomeEmail($customer);

        $this->assertSame($customer->customer_id, $mailable->customer->customer_id);
    }

    // -------------------------------------------------------------------------
    // Queue
    // -------------------------------------------------------------------------

    #[Test]
    /** Mailable implements ShouldQueue. */
    public function mailable_implements_should_queue(): void
    {
        $customer = Customer::factory()->create();
        $mailable = new WelcomeEmail($customer);

        $this->assertInstanceOf(ShouldQueue::class, $mailable);
    }

    // -------------------------------------------------------------------------
    // Envelope
    // -------------------------------------------------------------------------

    #[Test]
    /** Envelope subject contains business name. */
    public function envelope_subject_contains_business_name(): void
    {
        config(['business.name' => 'Test Business']);
        $customer = Customer::factory()->create();
        $mailable = new WelcomeEmail($customer);

        $this->assertSame(
            'Welcome to Test Business',
            $mailable->envelope()->subject,
        );
    }

    // -------------------------------------------------------------------------
    // Content
    // -------------------------------------------------------------------------

    #[Test]
    /** Content view is emails.customer.welcome. */
    public function content_view_is_customer_welcome(): void
    {
        $customer = Customer::factory()->create();
        $mailable = new WelcomeEmail($customer);

        $this->assertSame(
            'emails.customer.welcome',
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
        $customer = Customer::factory()->create();
        $mailable = new WelcomeEmail($customer);

        $this->assertSame([], $mailable->attachments());
    }
}