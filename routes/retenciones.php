<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\contable\retenciones\RetencionesController;

/* RETENCIONES */
Route::resource('/retenciones', RetencionesController::class)->except(['update', 'edit', 'show', 'destroy']);

Route::controller(RetencionesController::class)->group(function () {
    Route::get('/api/retenciones/personas', 'devolverPersonasRetenciones')->name('retenciones.devolverPersonasRetenciones');
    Route::get('/api/retenciones/base-porcentual', 'devolverBasePorcentual')->name('retenciones.devolverBasePorcentual');
    Route::get('/api/retenciones/comprobante', 'verificarComprobante')->name('retenciones.verificarComprobante');
    Route::get('/api/retenciones/tabla', 'tablaRetenciones')->name('retenciones.tablaRetenciones');
    Route::get('/api/retenciones/tabla/modificar/{id}', 'comprobantesPorId')->name('retenciones.comprobantesPorId');
    Route::put('/retenciones/modificarRetencion', 'modificarRetencion')->name('retenciones.modificarRetencion');
    Route::get('retenciones/exportar/personas', 'exportarPersonas')->name('retenciones.exportarPersonas');
    Route::put('/retenciones/base-porcentual', 'updateBasePorcentual')->name('retenciones.updateBasePorcentual');

    Route::post('/retenciones/guardarRetencion', 'guardarRetencion')->name('retenciones.guardarRetencion');
    Route::post('/retenciones/suma-quincena', 'obtenerSumaQuincena')->name('retenciones.suma-quincena');
    Route::post('/retenciones/exportar', 'exportarRetenciones')->name('retenciones.exportar');
    Route::get('/retenciones/obtener-por-cuit', 'obtenerRetencionesCuit')->name('retenciones.obtenerPorCuit');
    Route::post('/retenciones/exportarExcel', 'exportarRetencionesExcel')->name('retenciones.exportarExcel');
});
