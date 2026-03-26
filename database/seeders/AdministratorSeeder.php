<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the initial administrator account required for the system.
 *
 * Credentials are read from environment variables. Do not hardcode passwords.
 * This seeder is idempotent: if the username already exists, no action is taken.
 *
 * Required environment variables:
 *   ADMIN_USERNAME – the administrator username
 *   ADMIN_PASSWORD – the password (minimum 8 characters)
 *
 * Optional:
 *   ADMIN_FULL_NAME – defaults to 'System Administrator'
 *
 * Run this seeder when provisioning a fresh environment:
 *   php artisan db:seed --class=AdministratorSeeder
 */
class AdministratorSeeder extends Seeder
{
    /**
     * Seed the administrator account using environment variables.
     *
     * @return void
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME');
        $password = env('ADMIN_PASSWORD');

        if (empty($username) || empty($password)) {
            throw new \RuntimeException(
                'AdministratorSeeder requires ADMIN_USERNAME and ADMIN_PASSWORD ' .
                'to be set in the environment.'
            );
        }

        if (strlen($password) < 8) {
            throw new \RuntimeException(
                'ADMIN_PASSWORD must be at least 8 characters.'
            );
        }

        if (Staff::where('username', $username)->exists()) {
            $this->command->info("Administrator account '{$username}' already exists. Skipping.");
            return;
        }

        Staff::create([
            'username'       => $username,
            'password'       => Hash::make($password),
            'role'           => StaffRole::Administrator,
            'full_name'      => env('ADMIN_FULL_NAME', 'System Administrator'),
            'account_status' => AccountStatus::Active,
        ]);

        $this->command->info("Administrator account '{$username}' created successfully.");
    }
}