<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MessageSender;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MessageSender::class, function ($app) {
            return new MessageSender();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
