<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\At_cl\Exportar_PDF_atcl\Pdf_venta;

/* LISTADO GENERAL DE VENTAS */
Route::get('/propiedades/Venta/Listado-view', [Pdf_venta::class, 'ViewPropiedadesVenta'])->name('propiedades.Venta.Listado-view');
Route::get('/propiedades/Venta/Estados-view', [Pdf_venta::class, 'generarPDFlistadoEstadosVenta'])->name('propiedades.Venta.Estados-view');
