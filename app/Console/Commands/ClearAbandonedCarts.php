<?php

namespace App\Console\Commands;

use App\Models\CartItem;
use App\Models\ShoppingCart;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Removes cart items from shopping carts that have not been updated within
 * a configurable number of days. Empty carts may optionally be deleted.
 *
 * Default threshold is 30 days since last_updated. This can be overridden
 * with the --days option.
 *
 * The --keep-carts flag retains the empty cart record; otherwise, the cart
 * is also deleted after its items are removed.
 *
 * The --dry-run option reports what would be removed without making changes.
 *
 * This command is intended to be scheduled, for example weekly.
 */
class ClearAbandonedCarts extends Command
{
    protected $signature = 'app:clear-abandoned-carts
                            {--days=30  : Remove items from carts not updated within this many days}
                            {--keep-carts : Retain empty ShoppingCart records after removing their items}
                            {--dry-run  : Report what would be removed without making changes}';

    protected $description = 'Remove items from shopping carts that have not been updated within a given number of days.';

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('--days must be a positive integer.');
            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);

        $abandonedCartIds = ShoppingCart::where('last_updated', '<', $cutoff)
            ->pluck('cart_id');

        if ($abandonedCartIds->isEmpty()) {
            $this->info("No abandoned carts found older than {$days} day(s). Nothing to clear.");
            return self::SUCCESS;
        }

        $itemCount = CartItem::whereIn('cart_id', $abandonedCartIds)->count();
        $cartCount = $abandonedCartIds->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$itemCount} item(s) across {$cartCount} cart(s) would be removed.");
            return self::SUCCESS;
        }

        CartItem::whereIn('cart_id', $abandonedCartIds)->delete();
        $this->info("Removed {$itemCount} item(s) from {$cartCount} abandoned cart(s).");

        if (! $this->option('keep-carts')) {
            ShoppingCart::whereIn('cart_id', $abandonedCartIds)->delete();
            $this->info("Removed {$cartCount} empty cart record(s).");
        }

        return self::SUCCESS;
    }
}