<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the cart_items table.
 *
 * Stores individual items within a customer's shopping cart. Each item
 * contains an immutable snapshot of the design as it existed when added to
 * the cart. This snapshot cannot be modified after creation.
 *
 * The preview_image_reference stores the cloud URL of a thumbnail image used
 * in the cart display and later transferred to order items for staff reference.
 *
 * The print_file_reference stores the print-ready image reference generated
 * for staff production use. This is kept separately from the preview image so
 * customer-facing display assets and staff printing assets remain distinct.
 *
 * Cart items are automatically deleted when their parent cart is deleted.
 * The restrict constraint on product_id prevents the deletion of a product
 * while it still appears in any active shopping cart.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('cart_item_id');

            $table->unsignedInteger('cart_id');
            $table->foreign('cart_id')
                ->references('cart_id')
                ->on('shopping_carts')
                ->onDelete('cascade');

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')
                ->references('product_id')
                ->on('customizable_print_products')
                ->onDelete('restrict');

            $table->unsignedSmallInteger('quantity');
            $table->longText('design_snapshot');
            $table->longText('preview_image_reference')->nullable();
            $table->longText('print_file_reference')->nullable();
            $table->dateTime('date_added');

            $table->index('cart_id', 'idx_cart_item_cart');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
