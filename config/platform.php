<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Who runs this instance
    |--------------------------------------------------------------------------
    |
    | The terms of service, the privacy notice and the structured data in the
    | page head all have to name a real operator, and the law requires the
    | address to be a real one. None of that belongs in the source, so it is
    | read from the environment; what is written below are placeholders that
    | make it obvious when an instance has not been configured yet.
    |
    */

    'operator' => [
        'name' => env('PLATFORM_OPERATOR_NAME', 'Operator Serwisu'),
        'address' => env('PLATFORM_OPERATOR_ADDRESS', 'ul. Przykładowa 1, 00-000 Miasto'),
        'city' => env('PLATFORM_OPERATOR_CITY', 'Miasto'),
        'postal_code' => env('PLATFORM_OPERATOR_POSTAL_CODE', '00-000'),
        'country' => env('PLATFORM_OPERATOR_COUNTRY', 'PL'),
    ],

    'contact' => [
        'email' => env('PLATFORM_EMAIL', 'kontakt@example.com'),
        'phone' => env('PLATFORM_PHONE', '+48 000 000 000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | The account the seeders create
    |--------------------------------------------------------------------------
    |
    | Only used by db:seed on a fresh installation. The password has no default
    | on purpose: seeding without setting one is meant to be inconvenient.
    |
    */

    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
        'name' => env('SEED_ADMIN_NAME', 'admin'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],

    // The ordinary accounts the demonstration seeder creates alongside it.
    'seed_user_password' => env('SEED_USER_PASSWORD'),
];
