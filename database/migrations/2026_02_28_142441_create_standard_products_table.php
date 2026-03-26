<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the standard_products table.
 *
 * Stores browse-only retail products displayed in the catalog for customer
 * reference. These products cannot be added to the shopping cart or ordered
 * through the platform.
 *
 * The display_price field shows a VAT-inclusive reference price to customers.
 * This price is informational only and is not used in checkout calculations,
 * which apply exclusively to customizable products with tiered pricing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->string('product_name', 100);
            $table->string('description', 2000)->nullable();
            $table->string('image_reference', 255)->nullable();
            $table->enum('visibility_status', ['Active', 'Inactive'])->default('Active');

            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('product_categories')
                  ->onDelete('set null');

            $table->decimal('display_price', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_products');
    }
};