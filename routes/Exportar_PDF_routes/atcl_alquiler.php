<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\At_cl\Exportar_PDF_atcl\Pdf_alquiler ;

/* LISTADO GENERAL DE ALQUILERES */
Route::get('/propiedades/Alquiler/Listado-view', [Pdf_alquiler::class, 'ViewPropiedadesAlquiler'])->name('propiedades.Alquiler.Listado-view');
Route::get('/propiedades/Alquiler/Estados-view', [Pdf_alquiler::class, 'generarPDFlistadoEstados'])->name('propiedades.Alquiler.Estados-view');


Route::get('/propiedades/pdf/pdfPlantillaPropiedad/{id}/{tipoBTN}', [Pdf_alquiler::class, 'generarPDFpantillaPropiedad'])->name('propiedades.pdf.pdfPlantillaPropiedad');
