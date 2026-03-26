<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | The default guard is set to 'customer' because most routes serve the
    | customer-facing storefront. Staff access the admin panel through a
    | separate guard and login route.
    |
    */

    'defaults' => [
        'guard' => 'customer',
        'passwords' => 'customers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Two separate guards are defined for the two distinct user types:
    |
    | customer - for registered customers authenticating via email
    | staff    - for employees and administrators authenticating via username
    |
    | Both use session drivers. Keeping them separate ensures customer sessions
    | cannot access staff routes and vice versa.
    |
    */

    'guards' => [
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],

        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Each guard is backed by its own Eloquent provider pointing to the
    | correct model and credential field.
    |
    | Customers identify with email address.
    | Staff identify with username.
    |
    */

    'providers' => [
        'customers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Customer::class,
        ],

        'staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset Configuration
    |--------------------------------------------------------------------------
    |
    | Password reset is supported for customers only. Staff passwords are
    | reset directly by administrators through the staff account management
    | interface.
    |
    | Tokens expire after 60 minutes as required by security specifications.
    | Throttle limits reset requests to one per minute.
    |
    */

    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Sensitive actions requiring password confirmation will prompt again
    | after this many seconds of inactivity.
    |
    */

    'password_timeout' => 10800,

];