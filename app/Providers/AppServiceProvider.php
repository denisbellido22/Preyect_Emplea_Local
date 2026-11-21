<?php

namespace App\Providers;

use App\Models\Oferta;
use App\Models\Oportunidad;
use App\Observers\OfertaObserver;
use App\Observers\OportunidadObserver;
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
        // Cada cambio en una publicación mantiene la portada al día en tiempo real.
        Oferta::observe(OfertaObserver::class);
        Oportunidad::observe(OportunidadObserver::class);
    }
}
