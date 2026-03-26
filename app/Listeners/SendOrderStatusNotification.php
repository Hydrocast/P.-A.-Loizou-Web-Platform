<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Sends an order status update email when an order status changes.
 *
 * Listens to the OrderStatusChanged event. The email is sent to the address
 * stored on the order at checkout, ensuring the notification reaches the
 * correct recipient even if the customer later updates their profile.
 *
 * Implements ShouldQueue so the email is dispatched asynchronously, not
 * blocking the staff response.
 *
 * Email notifications are intentionally sent only when:
 *   1. the status becomes Ready for Pickup, and
 *   2. staff explicitly requested that the customer be notified.
 */
class SendOrderStatusNotification implements ShouldQueue
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    /**
     * Handle the OrderStatusChanged event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        if (! $event->sendEmail) {
            return;
        }

        if ($event->newStatus !== OrderStatus::ReadyForPickup) {
            return;
        }

        $this->emailService->sendOrderStatusNotification($event->order);
    }
}