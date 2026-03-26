<?php

namespace App\Listeners;

use App\Models\Customer;
use App\Services\EmailService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Sends a welcome email to a newly registered customer.
 *
 * Listens to the Registered event, which is fired after account creation.
 * The email is sent asynchronously via the queue.
 *
 * Only handles Customer instances.
 */
class SendWelcomeEmail implements ShouldQueue
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    /**
     * Handle the Registered event.
     */
    public function handle(Registered $event): void
    {
        if (!$event->user instanceof Customer) {
            return;
        }

        $this->emailService->sendWelcomeEmail($event->user);
    }
}