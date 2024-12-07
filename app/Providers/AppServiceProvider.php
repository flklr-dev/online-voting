<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            Config::set('curl.verify', false);
            $this->app->bind('Laravel\Socialite\Two\GoogleProvider', function ($app) {
                $config = $app['config']['services.google'];
                return new \Laravel\Socialite\Two\GoogleProvider(
                    $app['request'], 
                    $config['client_id'],
                    $config['client_secret'],
                    $config['redirect'],
                    $config['guzzle'] ?? ['verify' => false]
                );
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
