<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the pricing_tiers table.
 *
 * Stores quantity-based pricing rules for customizable print products.
 * Each product may have up to five pricing tiers. Tiers must start at
 * quantity 1 and form a continuous range with no gaps or overlaps.
 * All unit prices include VAT as specified in Revision 2 of the requirements.
 *
 * Validation of tier structure (contiguous ranges, starting at 1, no overlaps,
 * maximum five tiers) is performed by the application layer.
 *
 * Pricing tiers are automatically deleted when their associated product is
 * deleted, as they have no meaning without the product they price.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->increments('tier_id');

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('customizable_print_products')
                  ->onDelete('cascade');

            $table->unsignedInteger('minimum_quantity');
            $table->unsignedInteger('maximum_quantity');
            $table->decimal('unit_price', 10, 2);

            // Optimizes the quantity-based lookup during checkout
            $table->index(['product_id', 'minimum_quantity', 'maximum_quantity'], 'idx_pricing_tier_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};