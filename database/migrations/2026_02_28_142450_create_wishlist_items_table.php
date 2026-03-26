<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the wishlist_items table.
 *
 * Stores associations between customers and products they have saved to their
 * wishlist. Both standard products and customizable products can be wishlisted.
 *
 * Because product IDs come from two separate tables, a database foreign key
 * constraint cannot be applied directly to the product_id column. The
 * product_type column indicates whether the product is a standard product or
 * a customizable product. Validation that the referenced product exists and
 * is active is performed by the application code.
 *
 * Wishlist items are automatically deleted when their owning customer account
 * is deleted. The unique constraint prevents a customer from adding the same
 * product to their wishlist multiple times.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->increments('wishlist_item_id');

            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')
                  ->references('customer_id')
                  ->on('customers')
                  ->onDelete('cascade');

            $table->unsignedInteger('product_id');
            $table->enum('product_type', ['standard', 'customizable']);
            $table->dateTime('date_added');

            // Prevents duplicate wishlist entries
            $table->unique(['customer_id', 'product_id', 'product_type'], 'uq_wishlist_customer_product');
            
            // Optimizes "get customer's wishlist" queries
            $table->index('customer_id', 'idx_wishlist_customer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};