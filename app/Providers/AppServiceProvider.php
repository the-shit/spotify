<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load environment variables from .env file if it exists
        if (file_exists(base_path('.env'))) {
            \Dotenv\Dotenv::createImmutable(base_path())->safeLoad();
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
