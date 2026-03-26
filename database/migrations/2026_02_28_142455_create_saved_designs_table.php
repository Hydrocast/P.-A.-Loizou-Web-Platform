<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the saved_designs table.
 *
 * Stores customer-saved design snapshots for future reuse and ordering.
 * Once created, the design data cannot be modified - this immutability is
 * enforced by the application code, not the database.
 *
 * The design_data field contains the complete canvas state as a JSON string.
 * The preview_image_reference stores the cloud URL of a thumbnail image
 * displayed in the customer's My Designs grid.
 *
 * Saved designs are automatically deleted when the owning customer account
 * is deleted. However, the restrict constraint on product_id prevents the
 * deletion of a customizable product while customers still have saved designs
 * referencing it, preserving the integrity of customer records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_designs', function (Blueprint $table) {
            $table->increments('design_id');
            $table->string('design_name', 100);

            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')
                  ->references('customer_id')
                  ->on('customers')
                  ->onDelete('cascade');

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('customizable_print_products')
                  ->onDelete('restrict');

            $table->longText('design_data');
            $table->longText('preview_image_reference')->nullable();
            $table->dateTime('date_created');

            $table->index('customer_id', 'idx_saved_design_customer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_designs');
    }
};