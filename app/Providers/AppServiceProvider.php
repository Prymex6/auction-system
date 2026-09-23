<?php

namespace App\Providers;

use App\Repositories\AuctionRepository;
use App\Repositories\BidRepository;
use App\Repositories\Contracts\AuctionRepositoryInterface;
use App\Repositories\Contracts\BidRepositoryInterface;
use App\Repositories\MessageRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ReportRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\UserRepository;
use App\Services\AdminService;
use App\Services\DeviceService;
use App\Services\MessageService;
use App\Services\NotificationContentService;
use App\Services\ReportService;
use App\Services\ReviewService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service bindings
        $this->app->singleton(MessageService::class);
        $this->app->singleton(ReviewService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(DeviceService::class);
        $this->app->singleton(AdminService::class);
        $this->app->singleton(ReportService::class);
        $this->app->singleton(NotificationContentService::class);

        // Repository bindings
        $this->app->singleton(MessageRepository::class);
        $this->app->singleton(ReviewRepository::class);
        $this->app->singleton(UserRepository::class);
        $this->app->singleton(ReportRepository::class);
        $this->app->singleton(NotificationRepository::class);
        $this->app->bind(BidRepositoryInterface::class, BidRepository::class);
        $this->app->bind(AuctionRepositoryInterface::class, AuctionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // (nadpisuje APP_LOCALE z .env)
        app()->setLocale('pl');

        //
    }
}
