<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the shopping_carts table.
 *
 * Stores the persistent shopping cart for each authenticated customer.
 * The unique constraint on customer_id ensures that each customer can have
 * at most one active cart at any time, as specified in the requirements.
 *
 * The cart is automatically deleted when its owning customer account is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->increments('cart_id');

            // Enforces one cart per customer at the database level
            $table->unsignedInteger('customer_id')->unique();
            $table->foreign('customer_id')
                  ->references('customer_id')
                  ->on('customers')
                  ->onDelete('cascade');

            $table->dateTime('last_updated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_carts');
    }
};