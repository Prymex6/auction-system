<?php

namespace App\Providers;

use App\Policies\AuctionPolicy;
use App\Policies\MessagePolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProviderAuthServiceProvider;

class AuthServiceProvider extends ServiceProviderAuthServiceProvider
{
    protected $policies = [
        'App\Models\Auction' => AuctionPolicy::class,
        'App\Models\Message' => MessagePolicy::class,
        'App\Models\Review' => ReviewPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
