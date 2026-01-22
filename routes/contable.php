<?php

use App\Http\Controllers\Contable\Sellado\RegistroSelladoController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\contable\sellado\SelladoController;
use Illuminate\Support\Facades\DB;

Route::resource('/sellado', SelladoController::class);
Route::post('guardar/guardarDatosDeCalculo', [SelladoController::class, 'guardarDatosCalculo'])->name('guardar.guardarDatosDeCalculo');

Route::resource('/registroSellado', RegistroSelladoController::class);
Route::controller(RegistroSelladoController::class)->group(function () {
    Route::delete('/registroSellado', 'destroy')->name('registroSellado.destroy');
    Route::post('/registroSellado/guardar', 'guardar_registroSellado')->name('registroSellado.guardar');
    Route::post('/calculoSellado', 'calculoSellado')->name('calculoSellado');
    Route::get('/exportar/registro-sellado', 'exportarRegistroSellado')->name('exportar.registroSellado');
});

Route::get('/db', function () {
    try {
        $results = DB::connection('mysql3')->select('valor_hoja');
        return $results;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

