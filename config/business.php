<?php

/**
 * P. & A. Loizou – business‑specific configuration.
 *
 * Values are read from environment variables with sensible defaults for local
 * development. Production deployments must define the required variables.
 */

return [

    // -------------------------------------------------------------------------
    // VAT rate
    // -------------------------------------------------------------------------

    /*
     * The VAT rate applied to orders, expressed as a percentage (e.g. 19 for 19%).
     * The rate is stored on each order at submission so historical orders reflect
     * the rate in effect at that time.
     */
    'vat_rate' => (float) env('VAT_RATE', 19),

    // -------------------------------------------------------------------------
    // Business contact information
    // -------------------------------------------------------------------------

    /*
     * Email address to which contact form submissions are forwarded.
     * Must be set in production via the BUSINESS_EMAIL environment variable.
     */
    'email' => env('BUSINESS_EMAIL', 'info@example.com'),

    /*
     * Business name used in outbound email subjects and footers.
     */
    'name' => env('BUSINESS_NAME', 'P. & A. Loizou'),

];