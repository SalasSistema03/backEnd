<?php

use App\Http\Controllers\turnos\TurnoController;
use Illuminate\Support\Facades\Route;

Route::resource('/turnos', TurnoController::class)->except(['show']);

Route::controller(TurnoController::class)->group(function () {
    Route::get('/turnos/llamado', 'llamado')->name('turnos.llamado');
    Route::post('/turnos/llamar', 'llamar')->name('turnos.llamar');
    Route::get('/turnos/pendientesAllamar', 'verTurnosPendientesAllamar')->name('turnos.pendientesAllamar');
    Route::get('/turnos/pendientesAFinalizar', 'mostrarTurnospendinatesAFinalizar')->name('turnos.pendientesAFinalizar');
    Route::put('turnos/finalizar/{id}', 'finalizar')->name('turnos.finalizar');
    Route::get('turnos/mostrar', 'mostrar')->name('turnos.mostrar');
});


