<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Sends an order confirmation email when an order is placed.
 *
 * Listens to the OrderPlaced event. The email is sent to the address
 * provided at checkout, which is stored frozen on the order record.
 *
 * Implements ShouldQueue so the email is dispatched asynchronously,
 * ensuring the customer receives the confirmation without delaying the
 * HTTP response.
 */
class SendOrderConfirmationEmail implements ShouldQueue
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    /**
     * Handle the OrderPlaced event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->load('items');

        $this->emailService->sendOrderConfirmation($order);
    }
}