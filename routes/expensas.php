<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\impuesto\ExpController;
use App\Services\impuesto\EXP\ProveedoresServices;


Route::get('/exp-administrador-consorcio/create', [ExpController::class, 'create'])->name('exp_administrador_consorcio.create');
Route::get('/exp-administrador-consorcio/CargarAdministradores', [ExpController::class, 'CargarAdministradores'])->name('exp_administrador_consorcio.CargarAdministradores');
Route::get('/exp-administrador-consorcio/filtro', [ExpController::class, 'filtro'])->name('exp_administrador_consorcio.filtro');

Route::get('/exp-unidades', [ExpController::class, 'PadronUnidades'])->name('exp_unidades');
Route::get('/exp-unidades/CargarUnidades', [ExpController::class, 'CargarUnidades'])->name('exp_unidades.CargarUnidades');
Route::get('/exp-unidades/filtro-completo', [ExpController::class, 'filtroUnidadesCompleto'])->name('exp_unidades.filtro.completo');
Route::post('/exp-unidades/completar-carga', [ExpController::class, 'completarCargaUnidades'])->name('exp_unidades_completar_carga');
Route::get('exp-unidades/actualizar-padron', [ExpController::class, 'actualizarPadronUnidades'])->name('actualizar_padron_unidades');
Route::get('exp-unidades/eliminar/{id}', [ExpController::class, 'eliminarUnidad'])->name('exp_unidades.eliminar');

Route::get('/exp-edificios', [ExpController::class, 'PadronEdificios'])->name('exp_edificios');
Route::post('/exp-edificios/cargar', [ExpController::class, 'PadronEdificiosCargar'])->name('exp_edificios.cargar');
Route::get('/exp-consorcio/filtro', [ExpController::class, 'filtroConsorcio'])->name('exp_consorcio.filtro');
Route::put('/exp-consorcio/actualizar', [ExpController::class, 'actualizarConsorcio'])->name('exp_consorcio.actualizar');

Route::get('/exp-broche-expensas', [ExpController::class, 'brocheExpensas'])->name('exp_broche_expensas');
Route::get('/exp-broche-expensas/buscar/{folio?}/{empresa?}/{edificio?}/{administrador?}', 
    [ExpController::class, 'brocheExpensasBuscar'])
    ->name('exp_broche_expensas.buscar');



Route::post('/exp-broche-expensas/guardar', [ExpController::class, 'brocheExpensasGuardar'])->name('exp_broche_expensas.guardar');
Route::post('/exp-broche-expensas/eliminar/{id}', [ExpController::class, 'brocheExpensasGuardar'])->name('exp_broche_expensas.eliminar');
Route::delete('/exp-broche-expensas/eliminar/{id}', [ExpController::class, 'eliminarBroche'])->name('exp-broche-expensas.eliminar');
Route::get('/filtro_broche_expensas', [ExpController::class, 'filtroBrocheExpensas'])->name('exp_broche_expensas.filtro');
Route::get('/exp-broche-expensas/descargar', [ExpController::class, 'descargarBrocheExpensas'])->name('exp_broche_expensas.descargar');

Route::post('/exp-edificio', [ExpController::class, 'storeEdificio'])->name('exp_edificio.store');
// ...existing code...