<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Clears expired password reset tokens from the customers table.
 *
 * Tokens that have exceeded their 60‑minute expiry are set to null.
 * This prevents accumulation of stale token data.
 *
 * The command can be run in dry‑run mode to see how many tokens would be cleared.
 * It should be scheduled daily via the Laravel scheduler.
 *
 * Exit codes:
 *   0 – completed successfully (including when no tokens were purged)
 */
class PurgeExpiredResetTokens extends Command
{
    protected $signature = 'app:purge-expired-reset-tokens
                            {--dry-run : Report how many tokens would be purged without making changes}';

    protected $description = 'Clear expired password reset tokens from the customers table.';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success)
     */
    public function handle(): int
    {
        $cutoff = Carbon::now();

        $query = Customer::whereNotNull('reset_token')
            ->whereNotNull('reset_token_expiry')
            ->where('reset_token_expiry', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No expired reset tokens found. Nothing to purge.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$count} expired reset token(s) would be purged.");
            return self::SUCCESS;
        }

        $query->update([
            'reset_token'        => null,
            'reset_token_expiry' => null,
        ]);

        $this->info("Purged {$count} expired reset token(s).");

        return self::SUCCESS;
    }
}