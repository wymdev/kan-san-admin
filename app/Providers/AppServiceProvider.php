<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Announcement;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        Mail::extend('brevo', function () {
            // It uses the key from your .env file
            return new BrevoApiTransport(config('services.brevo.key') ?? env('BREVO_KEY'));
        });
        Announcement::observe(AnnouncementObserver::class);
    }
}
