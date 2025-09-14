<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Teammate's User Module Web Service
    |--------------------------------------------------------------------------
    |
    | Configuration for consuming your teammate's User module RESTful API.
    | Your Payment module will call these endpoints to get user information.
    |
    */
    'user_module' => [
        // Base URL of your teammate's User service
        'base_url' => env('USER_SERVICE_BASE_URL', 'http://127.0.0.1:8000'),
        
        // API version (if applicable)
        'api_version' => env('USER_SERVICE_API_VERSION', 'v1'),
        
        // Timeout settings
        'timeout' => env('USER_SERVICE_TIMEOUT', 10),
        'connect_timeout' => env('USER_SERVICE_CONNECT_TIMEOUT', 5),
        
        // Authentication (if required)
        'api_key' => env('USER_SERVICE_API_KEY'),
        'api_secret' => env('USER_SERVICE_API_SECRET'),
      
      
        // Fallback settings
        'enable_fallback' => env('USER_SERVICE_FALLBACK_ENABLED', true),
        'fallback_user_name' => 'Unknown User',
    ],


    'service_api' => [
        'base'  => env('SERVICE_API_BASE', 'http://127.0.0.1:8000/api/v1'),
        'token' => env('SERVICE_API_TOKEN'),  
        'key'   => env('SERVICE_API_KEY'),     
    ],

];