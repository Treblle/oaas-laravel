<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Treblle OaaS API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Treblle OaaS API settings here. You can obtain your
    | API token from the Treblle Identity Dashboard under Developer settings.
    |
    */

    'api_token' => env('TREBLLE_OAAS_API_TOKEN'),

    'base_url' => env('TREBLLE_OAAS_BASE_URL', 'https://api-forge.treblle.com/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default request settings and timeouts.
    |
    */

    'timeout' => env('TREBLLE_OAAS_TIMEOUT', 30),

    'connect_timeout' => env('TREBLLE_OAAS_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Default Pagination
    |--------------------------------------------------------------------------
    |
    | Set default pagination limits for API requests.
    |
    */

    'default_limit' => env('TREBLLE_OAAS_DEFAULT_LIMIT', 20),

    'max_limit' => env('TREBLLE_OAAS_MAX_LIMIT', 50),
];