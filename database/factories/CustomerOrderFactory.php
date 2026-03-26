<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerOrder>
 *
 * Creates customer order records with valid default values.
 * Provides states for order statuses, phone number boundaries,
 * customer name boundaries, and monetary totals (zero, minimal, typical).
 * Boundary values: customer_name (2-50), customer_phone (8 digits).
 */
class CustomerOrderFactory extends Factory
{
    protected $model = CustomerOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vatRate = 19.0;
        $netAmount = round($this->faker->randomFloat(2, 5.00, 500.00), 2);
        $vatAmount = round($netAmount * ($vatRate / 100), 2);
        $totalAmount = round($netAmount + $vatAmount, 2);

        return [
            'customer_id' => Customer::factory(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->numerify('########'),
            'order_creation_timestamp' => \Carbon\Carbon::now()->subHours(rand(1, 48)),
            'order_status' => OrderStatus::Pending,
            'net_amount' => $netAmount,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'vat_rate' => $vatRate,
            'assigned_staff_id' => null,
            'staff_assignment_date' => null,
        ];
    }

    // -------------------------------------------------------------------------
    // Status states
    // -------------------------------------------------------------------------

    /** Set order status to Pending. */
    public function pending(): static
    {
        return $this->state(fn () => ['order_status' => OrderStatus::Pending]);
    }

    /** Set order status to Processing. */
    public function processing(): static
    {
        return $this->state(fn () => ['order_status' => OrderStatus::Processing]);
    }

    /** Set order status to Ready for Pickup. */
    public function readyForPickup(): static
    {
        return $this->state(fn () => ['order_status' => OrderStatus::ReadyForPickup]);
    }

    /** Set order status to Completed. */
    public function completed(): static
    {
        return $this->state(fn () => ['order_status' => OrderStatus::Completed]);
    }

    /** Set order status to Cancelled. */
    public function cancelled(): static
    {
        return $this->state(fn () => ['order_status' => OrderStatus::Cancelled]);
    }

    /** Assign the order to a specific staff member and set assignment date. */
    public function assignedTo(Staff $staff): static
    {
        return $this->state(fn () => [
            'assigned_staff_id' => $staff->staff_id,
            'staff_assignment_date' => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Phone number boundaries
    // -------------------------------------------------------------------------

    /** Phone number of 7 digits (below minimum) is invalid. */
    public function phoneTooShort(): static
    {
        return $this->state(fn () => ['customer_phone' => '1234567']);
    }

    /** Phone number of exactly 8 digits (minimum) is valid. */
    public function phoneExactLength(): static
    {
        return $this->state(fn () => ['customer_phone' => $this->faker->numerify('########')]);
    }

    /** Phone number of 9 digits (above maximum) is invalid. */
    public function phoneTooLong(): static
    {
        return $this->state(fn () => ['customer_phone' => '123456789']);
    }

    // -------------------------------------------------------------------------
    // Customer name boundaries
    // -------------------------------------------------------------------------

    /** Customer name of 1 character (below minimum) is invalid. */
    public function customerNameTooShort(): static
    {
        return $this->state(fn () => ['customer_name' => 'A']);
    }

    /** Customer name of 2 characters (minimum) is valid. */
    public function customerNameMinLength(): static
    {
        return $this->state(fn () => ['customer_name' => 'Ab']);
    }

    /** Customer name of 26 characters (in-range) is valid. */
    public function customerNameMidLength(): static
    {
        return $this->state(fn () => ['customer_name' => str_repeat('a', 26)]);
    }

    /** Customer name of 50 characters (maximum) is valid. */
    public function customerNameMaxLength(): static
    {
        return $this->state(fn () => ['customer_name' => str_repeat('a', 50)]);
    }

    /** Customer name of 51 characters (above maximum) is invalid. */
    public function customerNameTooLong(): static
    {
        return $this->state(fn () => ['customer_name' => str_repeat('a', 51)]);
    }

    // -------------------------------------------------------------------------
    // Monetary boundaries – net + vat = total invariant
    // -------------------------------------------------------------------------

    /** Set all monetary values to zero. */
    public function zeroTotal(): static
    {
        return $this->state(fn () => [
            'net_amount' => 0.00,
            'vat_amount' => 0.00,
            'total_amount' => 0.00,
        ]);
    }

    /** Set total to 0.01 (minimum) with recalculated net and vat. */
    public function minimalTotal(): static
    {
        $vatRate = 19.0;
        $total = 0.01;
        $vat = round($total * ($vatRate / (100 + $vatRate)), 2);
        $net = round($total - $vat, 2);

        return $this->state(fn () => [
            'net_amount' => $net,
            'vat_amount' => $vat,
            'total_amount' => $total,
        ]);
    }

    /** Set total to 100.00 (typical) with recalculated net and vat. */
    public function typicalTotal(): static
    {
        $vatRate = 19.0;
        $total = 100.00;
        $vat = round($total * ($vatRate / (100 + $vatRate)), 2);
        $net = round($total - $vat, 2);

        return $this->state(fn () => [
            'net_amount' => $net,
            'vat_amount' => $vat,
            'total_amount' => $total,
        ]);
    }
}