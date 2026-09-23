<?php

return [
    'anti_sniper_period' => env('ANTI_SNIPER_PERIOD', 5), // minutes
    'auto_extension_enabled' => env('AUTO_EXTENSION_ENABLED', true),
    'minimum_bid_increment' => env('MINIMUM_BID_INCREMENT', 0.5), // percentage
    'commission_percentage' => env('COMMISSION_PERCENTAGE', 5), // percent
    'auction_duration' => [
        'default' => env('AUCTION_DURATION_DEFAULT', 7), // days
        'minimum' => env('AUCTION_DURATION_MIN', 1),
        'maximum' => env('AUCTION_DURATION_MAX', 30),
    ],
    'listing_limits' => [
        'free_user' => env('LISTING_LIMIT_FREE', 5),
        'premium_user' => env('LISTING_LIMIT_PREMIUM', 999), // unlimited
    ],
];
