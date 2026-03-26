<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the carousel_slides table.
 *
 * Stores promotional slides displayed in the rotating homepage carousel.
 * Each slide may optionally link to a product. When linked, the product_type
 * indicates whether it is a standard product or a customizable product.
 *
 * Because the product link can reference two different tables, a database
 * foreign key constraint cannot be applied. Referential integrity is
 * maintained by the application code, which nullifies the product reference
 * if the linked product is deactivated or deleted.
 *
 * Slides are ordered by display_sequence, which determines their order
 * of appearance in the carousel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_slides', function (Blueprint $table) {
            $table->increments('slide_id');
            $table->string('title', 50);
            $table->string('description', 100)->nullable();
            $table->string('image_reference', 255)->nullable();

            // Optional product link - references either product table
            $table->unsignedInteger('product_id')->nullable();
            $table->enum('product_type', ['standard', 'customizable'])->nullable();

            $table->unsignedInteger('display_sequence');

            $table->index('display_sequence', 'idx_carousel_sequence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_slides');
    }
};