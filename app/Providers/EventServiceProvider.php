<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\SendOrderStatusNotification;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Registers all application event-to-listener mappings.
 *
 * Three main event flows are defined:
 *   - Registered -> SendWelcomeEmail
 *   - OrderPlaced -> SendOrderConfirmationEmail
 *   - OrderStatusChanged -> SendOrderStatusNotification
 *
 * All listeners are queued to run asynchronously, preventing email dispatch
 * from blocking HTTP responses.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendWelcomeEmail::class,
        ],
        OrderPlaced::class => [
            SendOrderConfirmationEmail::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
        ],
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        static::disableEventDiscovery();
    }
}