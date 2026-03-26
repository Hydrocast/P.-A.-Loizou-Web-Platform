<?php

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Creates a new Administrator staff account.
 *
 * This command can be used interactively or non-interactively via options.
 * It validates inputs using the same rules as the create staff account form.
 *
 * Options:
 *   --username   : Administrator username (required)
 *   --password   : Password (min 8 characters, required)
 *   --full-name  : Optional full name
 *
 * Exit codes:
 *   0 – account created successfully
 *   1 – validation failed or username already exists
 */
class CreateAdministrator extends Command
{
    protected $signature = 'app:create-administrator
                            {--username=    : Username for the new administrator account}
                            {--password=    : Password (min 8 characters)}
                            {--full-name=   : Optional full name}';

    protected $description = 'Create a new Administrator staff account interactively or via options.';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 success, 1 failure)
     */
    public function handle(): int
    {
        $this->info('Creating a new Administrator account.');
        $this->newLine();

        $username = $this->option('username')
            ?: $this->ask('Username');

        $password = $this->option('password')
            ?: $this->secret('Password (min 8 characters)');

        $fullName = $this->option('full-name')
            ?: $this->ask('Full name (optional, press Enter to skip)') ?: null;

        $validator = Validator::make(
            ['username' => $username, 'password' => $password, 'full_name' => $fullName],
            [
                'username'  => ['required', 'string', 'max:100'],
                'password'  => ['required', 'string', 'min:8', 'max:64'],
                'full_name' => ['nullable', 'string', 'max:100'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        if (Staff::where('username', $username)->exists()) {
            $this->error("A staff account with username \"{$username}\" already exists.");
            return self::FAILURE;
        }

        $staff = Staff::create([
            'username'       => $username,
            'password'       => Hash::make($password),
            'role'           => StaffRole::Administrator,
            'full_name'      => $fullName,
            'account_status' => AccountStatus::Active,
        ]);

        $this->newLine();
        $this->info('Administrator account created successfully.');
        $this->table(
            ['Field', 'Value'],
            [
                ['Staff ID',   $staff->staff_id],
                ['Username',   $staff->username],
                ['Role',       $staff->role->value],
                ['Full Name',  $staff->full_name ?? '(not set)'],
                ['Status',     $staff->account_status->value],
            ]
        );

        return self::SUCCESS;
    }
}