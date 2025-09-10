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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'wordpress' => [
        'client_id'     => env('WP_CLIENT_ID'),
        'client_secret' => env('WP_CLIENT_SECRET'),
        'redirect'      => env('WP_REDIRECT_URI'),
        'scope'         => env('WP_SCOPE', 'auth'),
        'site_id'       => env('WP_SITE_ID'),
        'site'          => env('WP_SITE'),
        'api_base_url'  => env('WP_API_BASE_URL', 'https://public-api.wordpress.com/'),
        'require_admin' => env('WP_REQUIRE_ADMIN', false),
        'oauth' => [
            'authorize' => 'oauth2/authorize',
            'token'     => 'oauth2/token',
            'token_info'=> 'oauth2/token-info',
        ],
        'api' => [
            'site'      => 'rest/v1.1/sites',
            'me'        => 'rest/v1.4/me',
            'me_sites'  => 'rest/v1.1/me/sites',
        ],
    ],

];
