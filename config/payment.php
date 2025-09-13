<?php

// config/payment.php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Payment Methods
    |--------------------------------------------------------------------------
    |
    | This array defines all available payment methods and their corresponding
    | strategy classes. Add new payment methods here and they will automatically
    | be available throughout the application.
    |
    */
    'methods' => [
        'cash' => [
            'strategy' => \App\Payments\CashPaymentStrategy::class,
            'enabled' => true,
            'order' => 1,
        ],
        'credit_card' => [
            'strategy' => \App\Payments\CreditCardPaymentStrategy::class,
            'enabled' => true,
            'order' => 2,
        ],
        'paypal' => [
            'strategy' => \App\Payments\PayPalPaymentStrategy::class,
            'enabled' => true,
            'order' => 3,
        ],
        'bank_transfer' => [
            'strategy' => \App\Payments\BankTransferPaymentStrategy::class,
            'enabled' => true,
            'order' => 4,
        ],
       
        // Add more payment methods here...
        // 'stripe' => [
        //     'strategy' => \App\Payments\StripePaymentStrategy::class,
        //     'enabled' => env('STRIPE_ENABLED', false),
        //     'order' => 7,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Method Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'default_currency' => 'USD',
        'currency_symbol' => '$',
        
        // Cash payment settings
        'cash' => [
            'instructions' => 'Please bring exact amount or have change ready.',
            'max_amount' => 500.00, // Maximum amount for cash payments
        ],
        
        // Credit card settings
        'credit_card' => [
            'test_mode' => env('PAYMENT_TEST_MODE', true),
            'accepted_cards' => ['visa', 'mastercard', 'amex', 'discover'],
        ],
        
        // PayPal settings
        'paypal' => [
            'sandbox' => env('PAYPAL_SANDBOX', true),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        ],
        
        // Bank transfer settings
        'bank_transfer' => [
            'verification_required' => true,
            'processing_days' => '1-2 business days',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Processing Settings
    |--------------------------------------------------------------------------
    */
    'processing' => [
        'timeout' => 30, // seconds
        'retry_attempts' => 3,
        'simulate_failures' => env('SIMULATE_PAYMENT_FAILURES', false),
        'failure_rate' => 10, // percentage for simulation
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Configuration
    |--------------------------------------------------------------------------
    */
    'frontend' => [
        'show_icons' => true,
        'show_descriptions' => true,
        'enable_client_validation' => true,
        'load_external_scripts' => [
            'apple_pay' => 'https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js',
            'google_pay' => 'https://pay.google.com/gp/p/js/pay.js',
        ],
    ],
];