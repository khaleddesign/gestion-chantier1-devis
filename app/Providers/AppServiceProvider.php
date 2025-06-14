<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer le helper Tailwind comme singleton
        if (class_exists('App\Helpers\TailwindHelper')) {
            $this->app->singleton('tailwind', function () {
                return new \App\Helpers\TailwindHelper();
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuration supplémentaire si nécessaire
    }
}