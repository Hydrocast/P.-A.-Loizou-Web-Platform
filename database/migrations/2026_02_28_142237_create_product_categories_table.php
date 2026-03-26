<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the product_categories table.
 *
 * This table stores categories used to organize standard products in the catalog.
 * Customizable products do not use categories (as specified in the requirements).
 *
 * Categories with active products cannot be deleted - this is checked by the
 * application code, not the database. When a category is deleted (only allowed
 * if it has no active products), any products that were in that category will
 * have their category reference set to NULL, becoming "uncategorized".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('category_name', 50)->unique();
            $table->string('description', 500)->nullable();  
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};