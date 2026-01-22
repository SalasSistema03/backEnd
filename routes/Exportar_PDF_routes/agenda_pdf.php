<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\agenda\Exportar_PDF_agenda\Pdf_agenda;

/* LISTADO GENERAL DE ALQUILERES */

Route::get('/listados/agenda', [Pdf_agenda::class, 'index'])->name('agenda.pdf');
Route::get('/propiedades/asesorview', [Pdf_agenda::class, 'propiedaesPorAsesorPDF'])->name('propiedades.asesorview');
