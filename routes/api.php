<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\contable\sellado\SelladoController;
use App\Services\usuarios_y_permisos\PermisoService;
use App\Services\usuarios_y_permisos\UsuarioService;
use App\Http\Controllers\turnos\TurnoController;
use App\Http\Controllers\At_cl\CalleController;
use App\Http\Controllers\At_cl\Tipo_inmuebleController;
use App\Http\Controllers\At_cl\ZonaController;
use App\Http\Controllers\At_cl\ProvinciaController;
use App\Http\Controllers\At_cl\EstadoGeneralController;
use App\Http\Controllers\At_cl\EstadoVentaController;
use App\Http\Controllers\At_cl\UsuariosController;
use App\Http\Controllers\At_cl\EstadoAlquilerController;

Route::prefix('v1')->group(function () {

    // 1. GRUPO DE AUTENTICACIÓN (URL: api/v1/auth/...)
    // Solo para login y registro (Rutas públicas)
    Route::prefix('auth')->group(function () {
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
            //calles
            Route::get('calles', [CalleController::class, 'getCalles']);
            Route::get('tipos-inmueble', [Tipo_inmuebleController::class, 'getTiposInmueble']);
            Route::get('zonas', [ZonaController::class, 'getZonas']);
            Route::get('provincias', [ProvinciaController::class, 'getProvincias']);
            Route::get('estado-general', [EstadoGeneralController::class, 'getEstadoGeneral']);
            Route::get('estado-venta', [EstadoVentaController::class, 'getEstadoVenta']);
            Route::get('captador-interno', [UsuariosController::class, 'getCaptadorInterno']);
            Route::get('asesor', [UsuariosController::class, 'getAsesor']);
            Route::get('estado-alquiler', [EstadoAlquilerController::class, 'getEstadoAlquiler']);
        });
    });
    //CONTABLE - SELLADO
            Route::get('sellado', [SelladoController::class, 'getDatosSelladoController']);
});

    // 2. GRUPO PROTEGIDO (URL: api/v1/...)
    // Requieren Token, pero NO llevan "auth" en la URL
    Route::middleware('auth:api')->group(function () {
        
        // Sesión y Usuario
        Route::get('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        
        // Servicios de Navegación y Usuarios
        Route::get('nav', [PermisoService::class, 'getMenuData']);
        Route::get('permisos-navegacion', [PermisoService::class, 'getPermisosNavegacion']);
        Route::get('nombres-de-usuarios', [UsuarioService::class, 'getNombresDeUsuarios']);
        Route::get('datos-generales/{id_usuario}', [UsuarioService::class, 'getDatosGenerales']);
        Route::put('update-datos-generales/{id_usuario}', [UsuarioService::class, 'updateDatosGenerales']);

        // Turnos (URL: api/v1/turnos/...)
        Route::get('sectores', [TurnoController::class, 'getSectores']);
        Route::get('turnos/pendientes', [TurnoController::class, 'getTurnosPendientes']);
        Route::get('turnos/llamados', [TurnoController::class, 'getTurnosLlamados']);
        Route::get('turnos/completados', [TurnoController::class, 'getTurnosCompletados']);
        Route::post('turnos/cargar', [TurnoController::class, 'postCargarTurnoController']);
        Route::put('turnos/finalizar/{id}', [TurnoController::class, 'finalizarturno']);
        Route::put('turnos/llamar/{id}', [TurnoController::class, 'putLlamarTurno']);

        // CONTABLE - SELLADO (URL: api/v1/sellado)
        Route::get('sellado', [SelladoController::class, 'getDatosSelladoController']);
        Route::post('sellado/guardar-datos-calculo', [SelladoController::class, 'guardarDatosCalculoController']);
        
    }); // <--- Aquí cierra el middleware

}); // <--- Aquí cierra el prefijo v1

// Ruta de redirección por defecto si falla el token
Route::get('/', [AuthController::class, 'unauthorized'])->name('login');