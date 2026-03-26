<?php

namespace App\Services;

use App\Mail\ContactFormEmail;
use App\Mail\OrderConfirmationEmail;
use App\Mail\OrderStatusNotificationEmail;
use App\Mail\PasswordResetEmail;
use App\Mail\WelcomeEmail;
use App\Models\Customer;
use App\Models\CustomerOrder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

/**
 * Handles all outbound email delivery for the application.
 *
 * Emails are queued to prevent blocking HTTP responses.
 * The actual email templates are defined in App\Mail classes.
 */
class EmailService
{
    /**
     * Resolves the final recipient address for an outgoing message.
     */
    private function resolveRecipient(string $intendedRecipient): string
    {
        $overrideRecipient = config('mail.dev_override_address');

        if (App::environment('local') && ! empty($overrideRecipient)) {
            return $overrideRecipient;
        }

        return $intendedRecipient;
    }

    /**
     * Queues a welcome email for a newly registered customer.
     */
    public function sendWelcomeEmail(Customer $customer): void
    {
        Mail::to($this->resolveRecipient($customer->email))
            ->queue(new WelcomeEmail($customer));
    }

    /**
     * Queues a password reset email with a single-use token.
     */
    public function sendPasswordResetEmail(string $email, string $token): void
    {
        Mail::to($this->resolveRecipient($email))
            ->queue(new PasswordResetEmail($email, $token));
    }

    /**
     * Queues an order confirmation email.
     *
     * Uses the email address stored with the order at checkout,
     * not the customer's current profile email.
     */
    public function sendOrderConfirmation(CustomerOrder $order): void
    {
        Mail::to($this->resolveRecipient($order->customer_email))
            ->queue(new OrderConfirmationEmail($order));
    }

    /**
     * Queues a notification when an order status changes.
     */
    public function sendOrderStatusNotification(CustomerOrder $order): void
    {
        Mail::to($this->resolveRecipient($order->customer_email))
            ->queue(new OrderStatusNotificationEmail($order));
    }

    /**
     * Queues a contact form submission to the business email address.
     *
     * The recipient address is read from config('mail.from.address')
     * and can be changed per environment.
     */
    public function sendContactFormEmail(
        string $senderName,
        string $senderEmail,
        string $subject,
        string $message,
    ): void {
        $businessEmail = config('mail.from.address');

        Mail::to($this->resolveRecipient($businessEmail))
            ->queue(new ContactFormEmail($senderName, $senderEmail, $subject, $message));
    }
}