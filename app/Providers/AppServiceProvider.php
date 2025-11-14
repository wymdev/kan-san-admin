<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Announcement;
use App\Observers\AnnouncementObserver;
use App\Services\LotteryResultCheckerService;
use App\Services\PushNotificationService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(LotteryResultCheckerService::class, function ($app) {
            return new LotteryResultCheckerService();
        });
        
        $this->app->singleton(PushNotificationService::class, function ($app) {
            return new PushNotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Announcement::observe(AnnouncementObserver::class);
    }
}
