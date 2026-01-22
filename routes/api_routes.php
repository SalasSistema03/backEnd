<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\impuesto\TgiController;
use App\Http\Controllers\impuesto\ApiPadronController;
use App\Http\Controllers\impuesto\ApiController;

Route::get('/cargar_api', [ApiController::class, 'index'])->name('carga_api');
Route::post('/nuevo_api', [ApiController::class, 'cargarNuevoApi'])->name('cargarNuevoApi');
Route::get('/exportar_api_faltantes/{anio}/{mes}', [ApiController::class, 'exportarApiFaltantes'])->name('exportar_api_faltantes');
Route::delete('/api_carga/{id}', [ApiController::class, 'eliminarRegistro'])->name('eliminarRegistro_api');
Route::post('/api_carga_manual', [ApiController::class, 'cargarNuevoApiControllerManual'])->name('cargarNuevoApiControllerManual_api');
Route::get('/sumar_montos/api/{anio}/{mes}', [ApiController::class, 'sumarMontos'])->name('sumar_montos');
Route::get('/mostrar_broches/api/{anio}/{mes}/{cant_broches}', [ApiController::class, 'MostrarBroche'])->name('mostrar_broches');
Route::get('/guardar_num_broches/api/{anio}/{mes}/{cantidadBroches}', [ApiController::class, 'guardarBroches'])->name('guardar_num_broches');
Route::get('/guardar_num_broche_salas/api/{anio}/{mes}', [ApiController::class, 'guardarBrocheSALAS'])->name('guardar_num_broche_salas');
// Modificar estado de un registro de tgi_carga
Route::put('/modificar-estado/{id}', [ ApiController::class, 'modificarEstadoController'])->name('modificarEstado');
// Modificar bajado de un registro de tgi_carga
Route::get('/modificar_bajado/{anio}/{mes}', [ ApiController::class, 'modificarBajadoController'])->name('modificarBajado');

//Ruta para exportar broches
//

//Padron
Route::get('/padron_api', [ApiPadronController::class, 'index'])->name('padron_api');
Route::get('/actualizar_padron_api', [ApiPadronController::class, 'actualizarPadronAPI'])->name('actualizar_padron_api');
Route::put('/padron-api/actualizar', [ApiPadronController::class, 'actualizar'])->name('padron_api.actualizar');
//Esta ruta sirve para obtener un registro de tgi_padron por su folio y empresa
Route::get('/api_padron/obtener/{folio}/{empresa}', [ApiPadronController::class, 'obtenerRegistroPadronManual']); 
