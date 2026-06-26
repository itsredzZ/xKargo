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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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
    'osrm' => [
        'base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),
        'profile'  => env('OSRM_PROFILE', 'driving'),
        'speed_kmh' => env('OSRM_SPEED_KMH', 60),
    ],
    'streamlit' => [
        // Token yang dikirim Streamlit di header Authorization: Bearer ...
        // Harus sama persis dengan LARAVEL_API_TOKEN di .streamlit/secrets.toml
        'api_token'    => env('STREAMLIT_API_TOKEN', ''),

        // URL internal Streamlit (dipakai Nginx proxy & middleware)
        'internal_url' => env('STREAMLIT_INTERNAL_URL', 'http://127.0.0.1:8501'),

        // URL publik Streamlit (dipakai iFrame src di Blade views)
        // Jika pakai Nginx reverse proxy → http://localhost/streamlit
        // Jika dev tanpa Nginx → http://localhost:8501
        'public_url'   => env('STREAMLIT_PUBLIC_URL', 'http://localhost:8501'),
    ],

];
