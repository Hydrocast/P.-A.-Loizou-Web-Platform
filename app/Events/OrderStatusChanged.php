<?php

namespace App\Events;

use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a staff member updates an order's status.
 *
 * Contains the order and the new status. The new status is passed explicitly
 * because the listener needs to know which status was set, while the order
 * itself already reflects the update.
 *
 * sendEmail indicates whether the customer should be notified as part of
 * this status change. This allows staff to mark an order as Ready for Pickup
 * without automatically sending an email unless they explicitly choose to.
 *
 * The SendOrderStatusNotification listener subscribes to this event and queues
 * a notification email only when email sending was explicitly requested.
 */
class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CustomerOrder $order,
        public readonly OrderStatus $newStatus,
        public readonly bool $sendEmail = false,
    ) {}
}