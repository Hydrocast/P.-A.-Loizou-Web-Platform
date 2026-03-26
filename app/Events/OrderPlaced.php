<?php

namespace App\Events;

use App\Models\CustomerOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched after an order is successfully created and the cart cleared.
 *
 * Contains the complete order record. The SendOrderConfirmationEmail listener
 * handles this event to queue a confirmation email to the customer.
 *
 * SerializesModels allows the event to be queued safely.
 */
class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CustomerOrder $order,
    ) {}
}