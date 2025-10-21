<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema; // ← Ajout important
use Illuminate\Support\ServiceProvider;

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
        // Fix pour l'erreur "Specified key was too long"
        Schema::defaultStringLength(191);
    }
}
