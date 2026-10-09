<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FBR Digital Invoicing Gateway Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the FBR Digital Invoicing Gateway platform.
    |
    */
    'base_url' => env('FBR_DI_BASE_URL', 'https://invoicehub.pk/api'),

    /*
    |--------------------------------------------------------------------------
    | API Key (Bearer Token)
    |--------------------------------------------------------------------------
    |
    | Your Client API Key generated from the FBR Digital Invoicing Portal.
    | Obtain or manage your key at: https://invoicehub.pk/settings?tab=api
    |
    */
    'api_key' => env('FBR_DI_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment Mode
    |--------------------------------------------------------------------------
    |
    | 'production' - Dispatches invoices directly to FBR Live Production Gateway.
    | 'sandbox'    - Dispatches invoices to FBR PRAL Sandbox for compliance testing.
    |
    */
    'environment' => env('FBR_DI_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Default Seller Legal Details (Optional Overrides)
    |--------------------------------------------------------------------------
    |
    | By default, your seller NTN, business name, and address are automatically
    | resolved from your authenticated API Key profile on the gateway.
    | You may define fallback overrides here if necessary.
    |
    */
    'seller' => [
        'ntn_cnic' => env('FBR_SELLER_NTN', ''),
        'business_name' => env('FBR_SELLER_BUSINESS_NAME', ''),
        'province' => env('FBR_SELLER_PROVINCE', 'Punjab'),
        'address' => env('FBR_SELLER_ADDRESS', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum timeout in seconds when communicating with the FBR DI Gateway.
    |
    */
    'timeout' => (int) env('FBR_DI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Automatic Retries & Resilience
    |--------------------------------------------------------------------------
    |
    | Number of times to retry failed network requests before throwing exception.
    |
    */
    'retries' => (int) env('FBR_DI_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Optional settings for verifying incoming HMAC-SHA256 FBR webhook events.
    |
    */
    'webhooks' => [
        'secret' => env('FBR_DI_WEBHOOK_SECRET', ''),
        'path' => 'api/fbr-di/webhooks',
    ],
];
