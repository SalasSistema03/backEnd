<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\impuesto\TgiController;
use App\Http\Controllers\impuesto\TgiPadronController;

Route::get('/cargar_tgi', [TgiController::class, 'index'])->name('carga_tgi');
Route::post('/nuevo_tgi', [TgiController::class, 'cargarNuevoTGI'])->name('cargarNuevoTGI');
//esta ruta sirve para eliminar un registro de tgi_carga
Route::delete('/tgi_carga/{id}', [TgiController::class, 'eliminarRegistro'])->name('eliminarRegistro');
//Esta ruta sirve para guardar un registro de tgi_carga manual
Route::post('/tgi_carga_manual', [TgiController::class, 'cargarNuevoTgiControllerManual'])->name('cargarNuevoTgiControllerManual');
//Ruta para exportar tgi faltantes
Route::get('/exportar_tgi_faltantes/{anio}/{mes}', [TgiController::class, 'exportarTgiFaltantes'])->name('exportar_tgi_faltantes');
// Ruta para armar broches
Route::get('/armar_broches/{anio}/{mes}', [TgiController::class, 'armarBroches'])->name('armar_broches');
//Ruta para sumar montos de tgi cargados
Route::get('/sumar_montos/{anio}/{mes}', [TgiController::class, 'sumarMontos'])->name('sumar_montos');
//Ruta para guardar el numero de broches
Route::get('/mostrar_broches/{anio}/{mes}/{cant_broches}', [TgiController::class, 'MostrarBroche'])->name('mostrar_broches');
//Ruta para guardar el numero de broches (columna num_broche de tgi_carga)
Route::get('/guardar_num_broches/{anio}/{mes}/{cantidadBroches}', [TgiController::class, 'guardarBroches'])->name('guardar_num_broches');
// Modificar estado de un registro de tgi_carga
Route::put('/modificar-estado-tgi/{id}', [ TgiController::class, 'modificarEstadoTGIController'])->name('modificarEstadoTGI');
// Modificar bajado de un registro de tgi_carga
Route::get('/modificar_bajado/{anio}/{mes}', [ TgiController::class, 'modificarBajadoController'])->name('modificarBajado');

//Ruta para guardar el numero de broches (columna num_broche de tgi_carga) de SALAS
Route::get('/guardar_num_broche_salas/{anio}/{mes}', [TgiController::class, 'guardarBrocheSALAS'])->name('guardar_num_broche_salas');

//Ruta para exportar broches
//Route::get('/exportar_broches/{anio}/{mes}', [TgiController::class, 'exportarBroches'])->name('exportar_broches');

//Padron
Route::get('/padron_tgi', [TgiPadronController::class, 'index'])->name('padron_tgi');
Route::get('/actualizar_padron_tgi', [TgiPadronController::class, 'actualizarPadronTGI'])->name('actualizar_padron_tgi');
Route::put('/padron-tgi/actualizar', [TgiPadronController::class, 'actualizar'])->name('padron_tgi.actualizar');
//Esta ruta sirve para obtener un registro de tgi_padron por su folio y empresa
Route::get('/tgi_padron/obtener/{folio}/{empresa}', [TgiPadronController::class, 'obtenerRegistroPadronManual']);
