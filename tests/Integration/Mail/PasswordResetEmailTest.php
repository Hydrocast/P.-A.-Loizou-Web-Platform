<?php

namespace Tests\Integration\Mail;

use PHPUnit\Framework\Attributes\Test;
use App\Mail\PasswordResetEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

/**
 * Integration tests for the PasswordResetEmail mailable.
 *
 * Covers constructor property storage, queue implementation,
 * envelope configuration, content view, and attachments.
 */
class PasswordResetEmailTest extends TestCase
{
    private PasswordResetEmail $mailable;
    private string $email = 'customer@example.com';
    private string $token = 'raw-reset-token-abc123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailable = new PasswordResetEmail($this->email, $this->token);
    }

    // -------------------------------------------------------------------------
    // Constructor property storage
    // -------------------------------------------------------------------------

    #[Test]
    /** Constructor stores email on the mailable. */
    public function constructor_stores_email(): void
    {
        $this->assertSame($this->email, $this->mailable->email);
    }

    #[Test]
    /** Constructor stores raw token on the mailable. */
    public function constructor_stores_token(): void
    {
        $this->assertSame($this->token, $this->mailable->token);
    }

    // -------------------------------------------------------------------------
    // Queue
    // -------------------------------------------------------------------------

    #[Test]
    /** Mailable implements ShouldQueue. */
    public function mailable_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->mailable);
    }

    // -------------------------------------------------------------------------
    // Envelope
    // -------------------------------------------------------------------------

    #[Test]
    /** Envelope subject contains business name. */
    public function envelope_subject_contains_business_name(): void
    {
        config(['business.name' => 'Test Business']);

        $this->assertSame(
            'Reset Your Password — Test Business',
            $this->mailable->envelope()->subject,
        );
    }

    // -------------------------------------------------------------------------
    // Content
    // -------------------------------------------------------------------------

    #[Test]
    /** Content view is emails.customer.password-reset. */
    public function content_view_is_customer_password_reset(): void
    {
        $this->assertSame(
            'emails.customer.password-reset',
            $this->mailable->content()->view,
        );
    }

    // -------------------------------------------------------------------------
    // Attachments
    // -------------------------------------------------------------------------

    #[Test]
    /** Attachments returns an empty array. */
    public function attachments_returns_empty_array(): void
    {
        $this->assertSame([], $this->mailable->attachments());
    }
}