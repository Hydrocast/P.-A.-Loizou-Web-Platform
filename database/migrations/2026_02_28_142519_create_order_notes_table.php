<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the order_notes table.
 *
 * Stores internal staff notes associated with orders.
 *
 * Notes are automatically deleted when their parent order is deleted.
 * When a staff member is deleted, their notes remain but the staff_id
 * becomes null, preserving the note content while removing the association
 * to the deleted staff account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notes', function (Blueprint $table) {
            $table->increments('note_id');

            $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                  ->references('order_id')
                  ->on('customer_orders')
                  ->onDelete('cascade');

            $table->string('note_text', 1000);

            $table->unsignedInteger('staff_id')->nullable();
            $table->foreign('staff_id')
                  ->references('staff_id')
                  ->on('staff')
                  ->onDelete('set null');

            $table->dateTime('note_timestamp');

            $table->index('order_id', 'idx_order_note_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notes');
    }
};