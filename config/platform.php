<?php

return [
    'locales' => ['fa', 'en'],
    'operations_email' => env('OPERATIONS_EMAIL', env('MAIL_FROM_ADDRESS')),

    'default_locale' => env('APP_LOCALE', 'fa'),

    'super_user' => [
        'username' => env('SUPER_USER_USERNAME', 'superuser'),
        'email' => env('SUPER_USER_EMAIL', 'superuser@example.com'),
        'phone' => env('SUPER_USER_PHONE'),
        'password' => env('SUPER_USER_PASSWORD', 'password'),
    ],
];
