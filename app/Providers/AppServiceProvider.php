<?php

namespace App\Providers;

use App\Models\Team;
use Illuminate\Support\Facades\Route;
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
        // Páginas públicas: {championship} es el slug y solo resuelve campeonatos compartidos.
        // Uno privado responde 404, igual que uno que no existe.
        Route::bind('championship', fn (string $slug) => Team::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail());
    }
}
