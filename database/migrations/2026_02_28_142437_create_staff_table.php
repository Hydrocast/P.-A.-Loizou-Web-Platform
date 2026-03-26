<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the staff table.
 *
 * Stores staff accounts for both Employees and Administrators. Staff accounts
 * are created and managed exclusively by administrators (SRS F1.5, F1.6).
 *
 * The system must always retain at least one active Administrator account.
 * This constraint is enforced at the application layer (SRS §3.6.6, F1.6).
 * A staff member cannot deactivate their own account (SRS F1.6).
 *
 * Staff authenticate using a username, not an email address, and are served
 * through a separate authentication guard from customers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('staff_id');
            $table->string('username', 100)->unique();
            $table->string('password', 255);
            $table->enum('role', ['Employee', 'Administrator']);
            $table->string('full_name', 100)->nullable();
            $table->enum('account_status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            // Optimizes the "count active administrators" query
            // Used when enforcing the "at least one active admin" rule
            $table->index(['role', 'account_status'], 'idx_staff_role_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};