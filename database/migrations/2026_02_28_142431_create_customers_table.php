<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the customers table.
 *
 * This table stores registered customer accounts. It handles authentication,
 * profile information, and serves as the parent record for customer-owned
 * data including shopping carts, wishlists, saved designs, and order history.
 *
 * The account_status field enables account locking after failed login attempts
 * without requiring account deletion. This state persists across sessions.
 *
 * The reset_token and reset_token_expiry fields implement the password reset
 * functionality. Since password reset links are accessed without an active
 * session, the token must be stored in the database for verification. Tokens
 * expire after 60 minutes and are invalidated after a single use as required
 * by the security specifications.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('customer_id');
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('full_name', 50);
            $table->char('phone_number', 8)->nullable();    
            $table->enum('account_status', ['Active', 'Inactive'])->default('Active');
            $table->string('reset_token', 255)->nullable(); 
            $table->dateTime('reset_token_expiry')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};