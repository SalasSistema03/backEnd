<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Services\usuarios_y_permisos\PermisoService;
use App\Services\usuarios_y_permisos\UsuarioService;
use App\Http\Controllers\turnos\TurnoController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function(){
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
        Route::middleware('auth:api')->group(function(){
            Route::get('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
            Route::get('nav', [PermisoService::class, 'getMenuData']);
            Route::get('permisos-navegacion', [PermisoService::class, 'getPermisosNavegacion']);
            Route::get('nombres-de-usuarios', [UsuarioService::class, 'getNombresDeUsuarios']);
            Route::get('datos-generales/{id_usuario}', [UsuarioService::class, 'getDatosGenerales']);
            Route::put('update-datos-generales/{id_usuario}', [UsuarioService::class, 'updateDatosGenerales']);
            Route::get('sectores', [TurnoController::class, 'getSectores']);
            Route::get('turnos/pendientes', [TurnoController::class, 'getTurnosPendientes']);
            Route::get('turnos/llamados', [TurnoController::class, 'getTurnosLlamados']);
            Route::get('turnos/completados', [TurnoController::class, 'getTurnosCompletados']);
            Route::post('turnos/cargar', [TurnoController::class, 'postCargarTurnoController']);
            Route::put('turnos/finalizar/{id}', [TurnoController::class, 'finalizarturno']);
            Route::put('turnos/llamar/{id}', [TurnoController::class, 'putLlamarTurno']);
        });
    });
});


Route::get('/', [AuthController::class, 'unauthorized'])->name('login');
