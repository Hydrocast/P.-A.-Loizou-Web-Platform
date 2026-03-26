<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the order_items table.
 *
 * Stores individual items within a submitted order. All pricing and design
 * data is frozen at the time of order submission and must never be modified
 * after that point.
 *
 * The product name is copied from the product record at submission so that
 * the order accurately reflects what the product was called at the time of
 * purchase, even if the product is later renamed.
 *
 * Unit price and line subtotal are transferred directly from checkout and
 * match the values the customer reviewed before placing the order.
 *
 * The design snapshot is an immutable copy of the design, not a reference
 * to a saved design that could later be modified or deleted.
 *
 * The preview image reference is transferred from the cart item and used
 * for order detail thumbnails and staff print reference downloads.
 *
 * Order items are automatically deleted when their parent order is deleted.
 * The restrict constraint on product_id preserves product records for
 * historical order references, even if the product is no longer active.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('order_item_id');

            $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                  ->references('order_id')
                  ->on('customer_orders')
                  ->onDelete('cascade');

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('customizable_print_products')
                  ->onDelete('restrict');

            // Frozen product and pricing data - copied at order submission
            $table->string('product_name', 100);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedSmallInteger('quantity');
            $table->decimal('line_subtotal', 10, 2);

            $table->longText('design_snapshot');
            $table->longText('preview_image_reference')->nullable();

            $table->index('order_id', 'idx_order_item_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};