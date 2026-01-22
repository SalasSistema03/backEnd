<?php

use App\Http\Controllers\At_cl\PropiedadController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\clientes\CategoriasController;
use App\Http\Controllers\clientes\ClientesController;
use App\Http\Controllers\clientes\TipoInmuebleController;
use App\Http\Controllers\clientes\EnvioMailConsultas;


//CLIENTES.
Route::resource('/cargarcliente', ClientesController::class);
Route::get('/categorias', [CategoriasController::class, 'getCategorias'])->name('categorias');
Route::get('/tipo_inmueble', [TipoInmuebleController::class, 'getCategorias'])->name('tipo_inmueble');
Route::get('/propiedades/search', [PropiedadController::class, 'search'])->name('propiedades.search'); // Búsqueda de propiedades por código o dirección
Route::post('/envioMailConsultas', [EnvioMailConsultas::class, 'enviaMail'])->name('envioMailConsultas');

Route::controller(ClientesController::class)->group(function () {
    Route::post('/clientes/guardar', 'guardar')->name('clientes.guardar');
    Route::post('/clientes/alquiler/guardar', 'guardar')->name('clientes.guardar');
    Route::get('/categorias', 'getCategorias')->name('categorias');
    Route::get('cliente/{telefono?}', 'clientePorTelefono')->name('cliente.telefono');
    Route::put('/cliente/modificar/{cliente}', 'modificarDatosPersonales')->name('clientes.modificarDatosPersonales');
    Route::post('/clientes/alquiler/guardarCriteriosYpropiedades', 'guardarCriteriosYpropiedades')->name('clientes.guardarCriteriosYpropiedades');
});