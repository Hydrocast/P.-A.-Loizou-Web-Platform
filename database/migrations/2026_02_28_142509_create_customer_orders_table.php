<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the customer_orders table.
 *
 * Stores submitted customer orders. All contact information and pricing data
 * is frozen at the moment of order submission and must never be modified or
 * recalculated after that point.
 *
 * Newly submitted orders begin in Pending status, meaning the order has been
 * placed successfully and is awaiting staff review.
 *
 * Customer contact details are copied from the checkout form, not referenced
 * from the customers table. This ensures the order record remains accurate
 * even if the customer updates their profile information later.
 *
 * All monetary values are calculated during checkout review and stored with
 * the order. Prices include VAT as specified in Revision 2 of the requirements.
 *
 * Orders must be retained indefinitely for audit and reporting purposes.
 * Therefore, the restrict constraint prevents deletion of customers who have
 * order history. The staff assignment uses set null so that orders remain
 * intact if the assigned staff member's account is later removed.
 *
 * pickup_notification_sent_at stores the last time a pickup email was queued.
 * pickup_notification_sent_by_staff_id stores the staff member who triggered it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->increments('order_id');

            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')
                  ->references('customer_id')
                  ->on('customers')
                  ->onDelete('restrict');

            // Frozen customer contact data - copied at submission
            $table->string('customer_name', 50);
            $table->string('customer_email', 100);
            $table->char('customer_phone', 8);

            $table->dateTime('order_creation_timestamp');

            $table->enum('order_status', [
                'Pending',
                'Processing',
                'Ready for Pickup',
                'Completed',
                'Cancelled',
            ])->default('Pending');

            // Frozen pricing data - calculated once, never modified
            $table->decimal('net_amount', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('vat_rate', 5, 2);

            // Set null preserves order if assigned staff account is removed
            $table->unsignedInteger('assigned_staff_id')->nullable();
            $table->foreign('assigned_staff_id')
                  ->references('staff_id')
                  ->on('staff')
                  ->onDelete('set null');

            $table->dateTime('staff_assignment_date')->nullable();

            // Pickup email audit fields
            $table->timestamp('pickup_notification_sent_at')->nullable();
            $table->unsignedInteger('pickup_notification_sent_by_staff_id')->nullable();

            // Indexes support order management console filtering
            $table->index('customer_id', 'idx_order_customer');
            $table->index('order_status', 'idx_order_status');
            $table->index('order_creation_timestamp', 'idx_order_date');
            $table->index('assigned_staff_id', 'idx_order_staff');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};