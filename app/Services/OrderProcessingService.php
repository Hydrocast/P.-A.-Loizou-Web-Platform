<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Models\CustomerOrder;
use App\Models\OrderNote;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Handles order management operations performed by staff.
 *
 * Covers the full order lifecycle: listing, filtering, detail retrieval,
 * status updates, note creation, reassignment, and optional customer
 * pickup notification emails.
 *
 * Status changes still dispatch the OrderStatusChanged event. Customer email
 * sending is now explicit and staff-controlled:
 *   - when updating to Ready for Pickup, staff may choose whether to email
 *   - a pickup email may also be sent later manually
 */
class OrderProcessingService
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    /**
     * Retrieve active orders where status is not Completed or Cancelled.
     * This is the default view shown to staff on the order management console.
     */
    public function getActiveOrders(): Collection
    {
        return CustomerOrder::whereNotIn('order_status', [
            OrderStatus::Completed->value,
            OrderStatus::Cancelled->value,
        ])
            ->orderBy('order_creation_timestamp', 'desc')
            ->get();
    }

    /**
     * Retrieve orders matching the given filter criteria.
     *
     * All provided filters are applied using AND logic.
     * Any parameter may be null to skip that filter.
     */
    public function filterOrders(
        ?int $orderNumber,
        ?OrderStatus $status,
        ?\DateTimeInterface $startDate,
        ?\DateTimeInterface $endDate,
        string $sortOrder = 'desc',
    ): Collection {
        $query = CustomerOrder::query();

        if ($orderNumber !== null) {
            $query->where('order_id', $orderNumber);
        }

        if ($status !== null) {
            $query->where('order_status', $status->value);
        }

        if ($startDate !== null) {
            $query->where('order_creation_timestamp', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('order_creation_timestamp', '<=', $endDate);
        }

        $sortDirection = $sortOrder === 'asc' ? 'asc' : 'desc';

        return $query->orderBy('order_creation_timestamp', $sortDirection)->get();
    }

    /**
     * Retrieve a complete order record with all associated data for staff review.
     *
     * Delegates to loadOrder(), the single authoritative definition of a
     * fully-loaded order in this service.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the order does not exist
     */
    public function viewOrderDetails(int $orderId): CustomerOrder
    {
        return $this->loadOrder($orderId);
    }

    /**
     * Update the status of an order.
     *
     * If the new status is Ready for Pickup, staff may optionally choose to
     * send a pickup notification email immediately.
     *
     * The OrderStatusChanged event is always dispatched so other listeners or
     * future audit logic can still react to status updates.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the order does not exist
     * @throws ValidationException if email sending is requested for a non-pickup status
     */
    public function updateOrderStatus(
        int $orderId,
        OrderStatus $status,
        bool $sendEmail = false,
        ?int $staffId = null,
    ): void {
        $order = CustomerOrder::findOrFail($orderId);
        $oldStatus = $order->order_status;

        if ($sendEmail && $status !== OrderStatus::ReadyForPickup) {
            throw ValidationException::withMessages([
                'send_email' => 'Customer notification is only available for Ready for Pickup.',
            ]);
        }

        $order->update(['order_status' => $status]);

        if ($sendEmail) {
            $order->update([
                'pickup_notification_sent_at' => now(),
                'pickup_notification_sent_by_staff_id' => $staffId,
            ]);
        }

        Log::info('Order status updated by staff.', [
            'order_id' => $order->order_id,
            'staff_id' => $staffId,
            'old_status' => $oldStatus->value,
            'new_status' => $status->value,
            'send_email' => $sendEmail,
        ]);

        OrderStatusChanged::dispatch($order->fresh(), $status, $sendEmail);
    }

    /**
     * Manually send or resend a Ready for Pickup notification email.
     *
     * This supports the case where staff previously chose not to send the
     * email during the status change, or want to resend it later.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the order does not exist
     * @throws ValidationException if the order is not currently Ready for Pickup
     */
    public function sendPickupReadyEmail(int $orderId, ?int $staffId = null): void
    {
        $order = CustomerOrder::findOrFail($orderId);

        if ($order->order_status !== OrderStatus::ReadyForPickup) {
            throw ValidationException::withMessages([
                'order_status' => 'Pickup email can only be sent when the order is Ready for Pickup.',
            ]);
        }

        $this->emailService->sendOrderStatusNotification($order);

        $order->update([
            'pickup_notification_sent_at' => now(),
            'pickup_notification_sent_by_staff_id' => $staffId,
        ]);

        Log::info('Pickup email sent or resent by staff.', [
            'order_id' => $order->order_id,
            'staff_id' => $staffId,
            'order_status' => $order->order_status->value,
        ]);
    }

    /**
     * Add an internal staff note to an order.
     *
     * @throws ValidationException if the note text is empty or exceeds 1000 characters
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the order does not exist
     */
    public function addOrderNote(int $orderId, int $staffId, string $noteText): OrderNote
    {
        CustomerOrder::findOrFail($orderId);

        if (trim($noteText) === '' || mb_strlen($noteText) > 1000) {
            throw ValidationException::withMessages([
                'note_text' => 'Note text must be between 1 and 1000 characters.',
            ]);
        }

        $note = OrderNote::create([
            'order_id' => $orderId,
            'staff_id' => $staffId,
            'note_text' => $noteText,
            'note_timestamp' => now(),
        ]);

        Log::info('Order note added by staff.', [
            'order_id' => $orderId,
            'staff_id' => $staffId,
            'order_note_id' => $note->order_note_id,
        ]);

        return $note;
    }

    /**
     * Reassign an order to a different active staff member.
     *
     * @throws ValidationException if the specified staff member is not active
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the order or staff does not exist
     */
    public function reassignOrder(int $orderId, int $staffId): void
    {
        $order = CustomerOrder::findOrFail($orderId);
        $previousStaffId = $order->assigned_staff_id;

        if (! $this->validateStaffExists($staffId)) {
            throw ValidationException::withMessages([
                'staff_id' => 'The selected staff member is unavailable.',
            ]);
        }

        $order->update([
            'assigned_staff_id' => $staffId,
            'staff_assignment_date' => now(),
        ]);

        Log::info('Order reassigned to staff member.', [
            'order_id' => $order->order_id,
            'previous_staff_id' => $previousStaffId,
            'new_staff_id' => $staffId,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Returns true if the given staff member exists and is currently active.
     */
    private function validateStaffExists(int $staffId): bool
    {
        return Staff::where('staff_id', $staffId)
            ->where('account_status', AccountStatus::Active)
            ->exists();
    }

    /**
     * Load a complete order record with all relationships required for staff review.
     *
     * This is the single authoritative definition of a fully-loaded order in
     * this service. Any method needing a complete order record should call this.
     *
     * Relationships loaded:
     *   items.product    – order items with their product data
     *   notes.staff      – internal notes with the staff author
     *   assignedStaff    – the staff member currently assigned
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function loadOrder(int $orderId): CustomerOrder
    {
        return CustomerOrder::with([
            'items.product',
            'notes.staff',
            'assignedStaff',
        ])->findOrFail($orderId);
    }
}
