<?php

namespace Tests\Integration\Mail;

use App\Mail\ContactFormEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class ContactFormEmailTest extends TestCase
{
    private ContactFormEmail $mailable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailable = new ContactFormEmail(
            'John Doe',
            'john@example.com',
            'Need help',
            'Hello, I need help with my order.',
        );
    }

    public function test_constructor_stores_sender_name(): void
    {
        $this->assertEquals('John Doe', $this->mailable->senderName);
    }

    public function test_constructor_stores_sender_email(): void
    {
        $this->assertEquals('john@example.com', $this->mailable->senderEmail);
    }

    public function test_constructor_stores_subject(): void
    {
        $this->assertEquals('Need help', $this->mailable->contactSubject);
    }

    public function test_constructor_stores_message(): void
    {
        $this->assertEquals('Hello, I need help with my order.', $this->mailable->bodyMessage);
    }

    public function test_mailable_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->mailable);
    }

    public function test_envelope_subject_includes_contact_subject(): void
    {
        $envelope = $this->mailable->envelope();

        $this->assertEquals('Contact Form: Need help', $envelope->subject);
    }

    public function test_envelope_reply_to_contains_one_address(): void
    {
        $envelope = $this->mailable->envelope();

        $this->assertCount(1, $envelope->replyTo);
    }

    public function test_envelope_reply_to_address_is_sender_email(): void
    {
        $envelope = $this->mailable->envelope();

        $this->assertEquals('john@example.com', $envelope->replyTo[0]->address);
    }

    public function test_envelope_reply_to_name_is_sender_name(): void
    {
        $envelope = $this->mailable->envelope();

        $this->assertEquals('John Doe', $envelope->replyTo[0]->name);
    }

    public function test_content_view_is_contact_form_submission(): void
    {
        $content = $this->mailable->content();

        $this->assertEquals('emails.contact.form-submission', $content->view);
    }

    public function test_attachments_returns_empty_array(): void
    {
        $this->assertSame([], $this->mailable->attachments());
    }
}