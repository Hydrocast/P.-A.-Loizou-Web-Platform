<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Services\ContactService;
use App\Services\EmailService;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for ContactService.
 *
 * Covers contact form submission and all field validation.
 * Boundary values: name (2–50), subject (5–100), message (10–2000),
 * email (valid format required).
 */
class ContactServiceTest extends TestCase
{
    private ContactService $service;
    private EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailService = Mockery::mock(EmailService::class);
        $this->service      = new ContactService($this->emailService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // submitForm() — happy path
    // -------------------------------------------------------------------------

    #[Test]
    /** Valid submission dispatches one email with all correct arguments. */
    public function submit_form_sends_contact_form_email_when_all_fields_are_valid(): void
    {
        $this->emailService
            ->shouldReceive('sendContactFormEmail')
            ->once()
            ->with('Jane Doe', 'jane@example.com', 'Hello there', 'This is my test message body.');

        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello there', 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Email is not sent when validation fails on any field. */
    public function submit_form_does_not_send_email_when_validation_fails(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');

        $this->expectException(ValidationException::class);
        $this->service->submitForm('A', 'jane@example.com', 'Hello there', 'This is my test message body.');
    }

    // Name boundaries ---------------------------------------------------------

    #[Test]
    /** Name of 1 character (below minimum) is rejected. */
    public function submit_form_throws_when_name_is_one_character(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('A', 'jane@example.com', 'Hello there', 'This is my test message body.');
    }

    #[Test]
    /** Name of 2 characters (minimum) is accepted. */
    public function submit_form_accepts_name_of_two_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Ab', 'jane@example.com', 'Hello there', 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Name of 26 characters (in‑range) is accepted. */
    public function submit_form_accepts_name_of_twenty_six_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm(str_repeat('a', 26), 'jane@example.com', 'Hello there', 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Name of 50 characters (maximum) is accepted. */
    public function submit_form_accepts_name_of_fifty_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm(str_repeat('a', 50), 'jane@example.com', 'Hello there', 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Name of 51 characters (above maximum) is rejected. */
    public function submit_form_throws_when_name_exceeds_fifty_characters(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm(str_repeat('a', 51), 'jane@example.com', 'Hello there', 'This is my test message body.');
    }

    // Email validation --------------------------------------------------------

    #[Test]
    /** Invalid email format is rejected. */
    public function submit_form_throws_when_email_is_invalid(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('Jane Doe', 'not-an-email', 'Hello there', 'This is my test message body.');
    }

    #[Test]
    /** Empty email string is rejected. */
    public function submit_form_throws_when_email_is_empty(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('Jane Doe', '', 'Hello there', 'This is my test message body.');
    }

    // Subject boundaries ------------------------------------------------------

    #[Test]
    /** Subject of 4 characters (below minimum) is rejected. */
    public function submit_form_throws_when_subject_is_four_characters(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Help', 'This is my test message body.');
    }

    #[Test]
    /** Subject of 5 characters (minimum) is accepted. */
    public function submit_form_accepts_subject_of_five_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello', 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Subject of 52 characters (in‑range) is accepted. */
    public function submit_form_accepts_subject_of_fifty_two_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Jane Doe', 'jane@example.com', str_repeat('a', 52), 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Subject of 100 characters (maximum) is accepted. */
    public function submit_form_accepts_subject_of_one_hundred_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Jane Doe', 'jane@example.com', str_repeat('a', 100), 'This is my test message body.');
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Subject of 101 characters (above maximum) is rejected. */
    public function submit_form_throws_when_subject_exceeds_one_hundred_characters(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('Jane Doe', 'jane@example.com', str_repeat('a', 101), 'This is my test message body.');
    }

    // Message boundaries ------------------------------------------------------

    #[Test]
    /** Message of 9 characters (below minimum) is rejected. */
    public function submit_form_throws_when_message_is_nine_characters(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello there', str_repeat('a', 9));
    }

    #[Test]
    /** Message of 10 characters (minimum) is accepted. */
    public function submit_form_accepts_message_of_ten_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello there', str_repeat('a', 10));
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Message of 1005 characters (in‑range) is accepted. */
    public function submit_form_accepts_message_of_one_thousand_and_five_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello there', str_repeat('a', 1005));
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Message of 2000 characters (maximum) is accepted. */
    public function submit_form_accepts_message_of_two_thousand_characters(): void
    {
        $this->emailService->shouldReceive('sendContactFormEmail')->once();
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello there', str_repeat('a', 2000));
        
        // Assertion that no exception was thrown
        $this->assertTrue(true);
    }

    #[Test]
    /** Message of 2001 characters (above maximum) is rejected. */
    public function submit_form_throws_when_message_exceeds_two_thousand_characters(): void
    {
        $this->emailService->shouldNotReceive('sendContactFormEmail');
        $this->expectException(ValidationException::class);
        $this->service->submitForm('Jane Doe', 'jane@example.com', 'Hello there', str_repeat('a', 2001));
    }
}