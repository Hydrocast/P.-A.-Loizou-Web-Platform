<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the customizable_print_products table.
 *
 * This table stores products that customers can personalize through the
 * online design interface. Unlike standard products, these items:
 * - Do not have a fixed price (pricing is defined by quantity tiers)
 * - Are not assigned to categories (as specified in the requirements)
 *
 * Each customizable product stores product-specific catalog data, such as:
 * - product name
 * - description
 * - catalog image
 * - visibility status
 * - design profile key
 *
 * Shared designer behavior is resolved through design_profile_key.
 * This allows multiple customizable products to reuse the same:
 * - shirt color options
 * - mockup image set
 * - thumbnail image set
 * - designer workspace configuration
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customizable_print_products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->string('product_name', 100);
            $table->string('description', 2000)->nullable();
            $table->string('image_reference', 255)->nullable();
            $table->enum('visibility_status', ['Active', 'Inactive'])->default('Active');
            $table->string('design_profile_key', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customizable_print_products');
    }
};