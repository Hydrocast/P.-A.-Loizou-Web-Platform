<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Mail\ContactFormEmail;
use App\Mail\OrderConfirmationEmail;
use App\Mail\OrderStatusNotificationEmail;
use App\Mail\PasswordResetEmail;
use App\Mail\WelcomeEmail;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Unit tests for EmailService.
 *
 * Covers all email-sending methods: welcome, password reset,
 * order confirmation, order status notification, and contact form.
 */
class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Config::set('business.email', 'business@example.com');
        $this->service = new EmailService();
    }

    // -------------------------------------------------------------------------
    // sendWelcomeEmail()
    // -------------------------------------------------------------------------

    #[Test]
    /** Queues WelcomeEmail to the customer's email address. */
    public function send_welcome_email_queues_welcome_email_to_customer_address(): void
    {
        $customer = Customer::factory()->create(['email' => 'customer@example.com']);

        $this->service->sendWelcomeEmail($customer);

        Mail::assertQueued(WelcomeEmail::class, fn($mail) => $mail->hasTo($customer->email));
    }

    #[Test]
    /** Queues exactly one WelcomeEmail per call. */
    public function send_welcome_email_queues_exactly_one_mailable(): void
    {
        $customer = Customer::factory()->create();

        $this->service->sendWelcomeEmail($customer);

        Mail::assertQueued(WelcomeEmail::class, 1);
    }

    // -------------------------------------------------------------------------
    // sendPasswordResetEmail()
    // -------------------------------------------------------------------------

    #[Test]
    /** Queues PasswordResetEmail to the provided address. */
    public function send_password_reset_email_queues_to_provided_address(): void
    {
        $this->service->sendPasswordResetEmail('reset@example.com', 'raw-token-abc');

        Mail::assertQueued(PasswordResetEmail::class, fn($mail) => $mail->hasTo('reset@example.com'));
    }

    #[Test]
    /** Passes the raw token unchanged to the mailable. */
    public function send_password_reset_email_passes_raw_token_unchanged_to_mailable(): void
    {
        $this->service->sendPasswordResetEmail('reset@example.com', 'my-raw-token');

        Mail::assertQueued(PasswordResetEmail::class, fn(PasswordResetEmail $mail) => $mail->token === 'my-raw-token');
    }

    #[Test]
    /** Queues exactly one PasswordResetEmail per call. */
    public function send_password_reset_email_queues_exactly_one_mailable(): void
    {
        $this->service->sendPasswordResetEmail('reset@example.com', 'raw-token-abc');

        Mail::assertQueued(PasswordResetEmail::class, 1);
    }

    // -------------------------------------------------------------------------
    // sendOrderConfirmation()
    // -------------------------------------------------------------------------

    #[Test]
    /** Queues OrderConfirmationEmail to the frozen order email. */
    public function send_order_confirmation_queues_to_frozen_order_email(): void
    {
        $order = CustomerOrder::factory()->create(['customer_email' => 'buyer@example.com']);

        $this->service->sendOrderConfirmation($order);

        Mail::assertQueued(OrderConfirmationEmail::class, fn($mail) => $mail->hasTo($order->customer_email));
    }

    #[Test]
    /** Queues exactly one OrderConfirmationEmail per call. */
    public function send_order_confirmation_queues_exactly_one_mailable(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->service->sendOrderConfirmation($order);

        Mail::assertQueued(OrderConfirmationEmail::class, 1);
    }

    // -------------------------------------------------------------------------
    // sendOrderStatusNotification()
    // -------------------------------------------------------------------------

    #[Test]
    /** Queues OrderStatusNotificationEmail to the frozen order email. */
    public function send_order_status_notification_queues_to_frozen_order_email(): void
    {
        $order = CustomerOrder::factory()->create(['customer_email' => 'buyer@example.com']);

        $this->service->sendOrderStatusNotification($order);

        Mail::assertQueued(OrderStatusNotificationEmail::class, fn($mail) => $mail->hasTo($order->customer_email));
    }

    #[Test]
    /** Queues exactly one OrderStatusNotificationEmail per call. */
    public function send_order_status_notification_queues_exactly_one_mailable(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->service->sendOrderStatusNotification($order);

        Mail::assertQueued(OrderStatusNotificationEmail::class, 1);
    }

    // -------------------------------------------------------------------------
    // sendContactFormEmail()
    // -------------------------------------------------------------------------

    #[Test]
    /** Queues ContactFormEmail to the configured business email address. */
    public function send_contact_form_email_queues_to_business_email_not_sender(): void
    {
        $this->service->sendContactFormEmail(
            'John Doe',
            'john@example.com',
            'Enquiry',
            'Message text.'
        );

        Mail::assertQueued(ContactFormEmail::class, fn($mail) => $mail->hasTo('business@example.com'));
    }

    #[Test]
    /** Does not queue the email to the sender's address. */
    public function send_contact_form_email_does_not_queue_to_sender_address(): void
    {
        $this->service->sendContactFormEmail(
            'John Doe',
            'john@example.com',
            'Enquiry',
            'Message text.'
        );

        Mail::assertQueued(ContactFormEmail::class, fn($mail) => !$mail->hasTo('john@example.com'));
    }

    #[Test]
    /** Sets the sender's email address as the reply-to address. */
    public function send_contact_form_email_sets_reply_to_sender_email(): void
    {
        $this->service->sendContactFormEmail(
            'John Doe',
            'john@example.com',
            'Subject',
            'Message'
        );

        Mail::assertQueued(ContactFormEmail::class, fn(ContactFormEmail $mail) => $mail->hasReplyTo('john@example.com'));
    }

    #[Test]
    /** Reads the business email address from configuration. */
    public function send_contact_form_email_respects_configured_business_email(): void
    {
        Config::set('business.email', 'different-office@example.com');

        $this->service->sendContactFormEmail('Jane Smith', 'jane@example.com', 'Subject', 'Message');

        Mail::assertQueued(ContactFormEmail::class, fn($mail) => $mail->hasTo('different-office@example.com'));
    }

    #[Test]
    /** Queues exactly one ContactFormEmail per call. */
    public function send_contact_form_email_queues_exactly_one_mailable(): void
    {
        $this->service->sendContactFormEmail(
            'John Doe',
            'john@example.com',
            'Enquiry',
            'Message text.'
        );

        Mail::assertQueued(ContactFormEmail::class, 1);
    }
}