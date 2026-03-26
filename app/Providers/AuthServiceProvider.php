<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\CustomerOrder;
use App\Models\SavedDesign;
use App\Models\Staff;
use App\Policies\CartItemPolicy;
use App\Policies\DesignPolicy;
use App\Policies\OrderPolicy;
use App\Policies\StaffAccountPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * Registers application authorisation policies.
 *
 * Policies are mapped explicitly because several policy class names do not
 * follow Laravel's default auto-discovery naming convention.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CartItem::class => CartItemPolicy::class,
        CustomerOrder::class => OrderPolicy::class,
        SavedDesign::class => DesignPolicy::class,
        Staff::class => StaffAccountPolicy::class,
    ];

    /**
     * Register any authentication / authorisation services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}