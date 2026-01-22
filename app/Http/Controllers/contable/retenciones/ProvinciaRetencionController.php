<?php

namespace App\Http\Controllers\contable\retenciones;

use App\Http\Controllers\Controller;
use App\Models\Contable\retenciones\Provincia_retencion;

class ProvinciaRetencionController extends Controller
{
    public function devolverProvincias()
    {
        $provincias = Provincia_retencion::all();
        return response()->json($provincias);
    }

   
}