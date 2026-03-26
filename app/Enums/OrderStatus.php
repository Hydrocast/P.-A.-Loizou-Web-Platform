<?php

namespace App\Enums;

/**
 * Represents the lifecycle status of a customer order.
 *
 * Valid statuses:
 *   Pending         — order has been submitted and is awaiting staff review
 *   Processing      — order is actively being prepared
 *   Ready for Pickup — order is complete and available for in-store collection
 *   Completed       — customer has collected the order
 *   Cancelled       — order has been cancelled
 */
enum OrderStatus: string
{
    case Pending = 'Pending';
    case Processing = 'Processing';
    case ReadyForPickup = 'Ready for Pickup';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';

    /**
     * Returns the readable label for display in interfaces.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::ReadyForPickup => 'Ready for Pickup',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Returns true if the order is in a final state (Completed or Cancelled).
     * Final orders are excluded from the default staff order list.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled => true,
            default => false,
        };
    }

    /**
     * Returns the next logical status in the order workflow.
     * Returns null if there is no next status (Completed or Cancelled).
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::Processing,
            self::Processing => self::ReadyForPickup,
            self::ReadyForPickup => self::Completed,
            default => null,
        };
    }

    /**
     * Returns true if the order can still be modified before active work begins.
     */
    public function isEditable(): bool
    {
        return $this === self::Pending;
    }

    /**
     * Returns true if the order can be assigned to staff.
     */
    public function isAssignable(): bool
    {
        return match ($this) {
            self::Pending, self::Processing => true,
            default => false,
        };
    }

    /**
     * Returns all possible values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}