<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\agenda\AgendaController;
use App\Http\Controllers\agenda\NotasController;
use App\Http\Controllers\agenda\RecordatorioController;
use App\Http\Controllers\agenda\SectoresController;
use App\Models\agenda\Recordatorio;
use App\Models\At_cl\Usuario;


Route::resource('/agenda', AgendaController::class);
Route::post('/nota/marcar-realizada', [AgendaController::class, 'marcarRealizada'])->name('nota.marcarRealizada');

Route::resource('/sectores', SectoresController::class);
Route::resource('/notas', NotasController::class);
Route::resource('/recordatorio', RecordatorioController::class);
Route::post('/recordatorios/actualizar-automaticamente', [RecordatorioController::class, 'actualizarAutomaticamente'])
    ->name('recordatorio.actualizarAutomaticamente');
Route::post('/notificacion/marcar-leida', [RecordatorioController::class, 'marcarNotificacionLeida'])
    ->name('notificacion.marcarLeida');
Route::get('/clientes/historial', [AgendaController::class, 'obtenerHistorialCliente'])->name('clientes.historial');
