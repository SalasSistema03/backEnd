<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\At_cl\AuthenticationService;
use App\Models\usuarios_y_permisos\Permiso;
use App\Models\usuarios_y_permisos\Nav;
use App\Models\usuarios_y_permisos\Botones;
use App\Models\usuarios_y_permisos\Vista;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

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
        Paginator::useBootstrap();
        // Compartir los datos del modelo Permiso con todas las vistas
        View::share('permisos', Permiso::all());


    }
}
