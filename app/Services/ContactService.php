<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/**
 * Handles submission of the public contact form.
 *
 * Validates the submitted fields and delegates email transmission to
 * EmailService. Contact form submissions are not stored in the database.
 *
 * Authentication is not required. The contact form is accessible to all
 * visitors, including unauthenticated users.
 */
class ContactService
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {
        //
    }

    /**
     * Validates and transmits a contact form submission.
     *
     * @throws ValidationException on any validation failure
     */
    public function submitForm(
        string $fullName,
        string $email,
        string $subject,
        string $message,
    ): void {
        $this->validateFullName($fullName);
        $this->validateEmail($email);
        $this->validateSubject($subject);
        $this->validateMessage($message);

        $this->emailService->sendContactFormEmail($fullName, $email, $subject, $message);
    }

    /**
     * Validates full name is between 2 and 50 characters.
     *
     * @throws ValidationException
     */
    private function validateFullName(string $fullName): void
    {
        $length = mb_strlen(trim($fullName));

        if ($length < 2 || $length > 50) {
            throw ValidationException::withMessages([
                'fullName' => 'Full name must be between 2 and 50 characters.',
            ]);
        }
    }

    /**
     * Validates email format.
     *
     * @throws ValidationException
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => 'Please provide a valid email address.',
            ]);
        }
    }

    /**
     * Validates subject is between 5 and 100 characters.
     *
     * @throws ValidationException
     */
    private function validateSubject(string $subject): void
    {
        $length = mb_strlen(trim($subject));

        if ($length < 5 || $length > 100) {
            throw ValidationException::withMessages([
                'subject' => 'Subject must be between 5 and 100 characters.',
            ]);
        }
    }

    /**
     * Validates message is between 10 and 2000 characters.
     *
     * @throws ValidationException
     */
    private function validateMessage(string $message): void
    {
        $length = mb_strlen(trim($message));

        if ($length < 10 || $length > 2000) {
            throw ValidationException::withMessages([
                'message' => 'Message must be between 10 and 2000 characters.',
            ]);
        }
    }
}