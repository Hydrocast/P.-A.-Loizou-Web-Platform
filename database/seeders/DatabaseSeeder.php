<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Root seeder that orchestrates all application seeders.
 *
 * Run on a fresh deployment with:
 *   php artisan migrate --seed
 *
 * Seeder execution order:
 *
 *   1. AdministratorSeeder
 *      Creates the initial administrator account so the system is usable
 *      immediately after deployment. Requires ADMIN_USERNAME and ADMIN_PASSWORD
 *      environment variables.
 *
 *   2. ClipartSeeder
 *      Populates the clipart library. Clipart is defined at deployment time
 *      and is independent of other seeders.
 *
 * The following data are intentionally not seeded because they are created
 * through the application UI:
 *   - Product categories
 *   - Standard products
 *   - Carousel slides
 *   - Customer accounts
 *
 * One initial customizable product is seeded separately because the design
 * workspace requires a ready-to-use profile-backed product for deployment.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->call([
            AdministratorSeeder::class,
            ClipartSeeder::class,
            CustomizableUnisexTShirtSeeder::class,
            CustomizableWomenMediumFitTShirtSeeder::class,
            CustomizableKidsTShirtSeeder::class,
            CustomizableMenRegularFitPoloTShirtSeeder::class,
            CustomizableWomenRegularFitPoloTShirtSeeder::class,
            CustomizableKidsPoloTShirtSeeder::class,
            CustomizableUnisexAdultsHoodieSeeder::class,
        ]);
    }
}
