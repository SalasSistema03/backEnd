<?php

use App\Http\Controllers\At_cl\DocumentacionController;
use App\Http\Controllers\At_cl\FotoController;
use App\Http\Controllers\At_cl\PadronController;
use App\Http\Controllers\At_cl\PropiedadController;
use App\Http\Controllers\At_cl\PropiedadesPadronController;
use App\Http\Controllers\At_cl\RegistroController;
use App\Http\Controllers\At_cl\Tipo_inmuebleController;
use App\Http\Controllers\At_cl\VideoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ValidacionesController;
use Illuminate\Support\Facades\Route;


Route::get('home', [HomeController::class, 'index'])->name('home');


Route::resource('/padron', PadronController::class);
Route::post('/guardar-propietarios', [PadronController::class, 'guardarPropietarios'])->name('guardar_propietarios');

Route::resource('/propiedad', PropiedadController::class);
Route::controller(PropiedadController::class)->group(function () {
    Route::post('/guardar-cambio', 'guardarCambio')->name('propiedad.guardarCambio');
    Route::put('/propiedad/{id}/updatedatos', 'updatedatos')->name('propiedad.updatedatos');
    Route::post('/propiedad/asignar-persona', 'asignarPersona')->name('propiedad.asignarPersona');
    Route::post('/propiedad/dar-de-baja', 'darDeBaja')->name('propiedad.darDeBaja');
    Route::post('/propiedad/dar-de-alta', 'darDeAlta')->name('propiedad.darDeAlta');
    Route::get('/propiedad/cargaPropietario', 'cargaPropietario')->name('atencionAlCliente.propiedad.cargaPropietario');
    Route::post('descargar-fotos/{id}', 'descargarFotos')->name('descargar-fotos');
});


Route::resource('/propiedad_padron', PropiedadesPadronController::class);
Route::controller(PropiedadesPadronController::class)->group(function () {
    Route::post('/vincular-propietario', 'vincular')->name('vincular.propietario');
    Route::delete('/desvincular-propietario', 'desvincular')->name('desvincular.propietario');
});
   
Route::resource('/tipo_inmueble', Tipo_inmuebleController::class);

Route::resource('/registro', RegistroController::class);

Route::resource('/validaciones', ValidacionesController::class); // Ensure the ValidacionesController class is defined and properly imported

Route::resource('/fotos', FotoController::class);

Route::resource('/documentacion', DocumentacionController::class);

Route::resource('video', VideoController::class);

