<?php

use Illuminate\Support\Facades\Route;


/* Ruta de principal por defecto */

Route::get('/{any?}', function () {
    return view('app'); // Aquí carga el HTML de Vue
})->where('any', '^(?!api).*$'); // Esto ignora las rutas que empiezan con /api

