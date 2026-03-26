<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the clipart table.
 *
 * Stores the reusable graphical assets available in the product customisation
 * workspace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clipart', function (Blueprint $table) {
            $table->increments('clipart_id');
            $table->string('clipart_name', 100);
            $table->string('image_reference', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clipart');
    }
};