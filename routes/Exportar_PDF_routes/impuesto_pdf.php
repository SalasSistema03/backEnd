<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\impuesto\Exportar_PDF_impuesto\Pdf_Tgi;
use App\Http\Controllers\impuesto\Exportar_PDF_impuesto\Pdf_Api;

/* Exportar pdf de tgi */
Route::get('/exportar_broches/{anio}/{mes}', [Pdf_Tgi::class, 'PDF_broche'])->name('exportar_broches');
Route::get('/exportar_broches_salas/{anio}/{mes}', [Pdf_Tgi::class, 'PDF_BorcheSalas'])->name('exportar_broches_salas');
Route::get('/exportar_broches_api/{anio}/{mes}', [Pdf_Api::class, 'PDF_broche_api'])->name('exportar_broches_api');
Route::get('/exportar_broches_salas_api/{anio}/{mes}', [Pdf_Api::class, 'PDF_BorcheSalas_api'])->name('exportar_broches_salas_api');

