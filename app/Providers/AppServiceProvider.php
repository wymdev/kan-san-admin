<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Announcement;
use App\Observers\AnnouncementObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
