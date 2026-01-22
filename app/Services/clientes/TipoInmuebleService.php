<?php

namespace App\Services\clientes;

use App\Models\At_cl\Tipo_inmueble;
//use App\Models\cliente\tipo_inmueble as ClienteTipo_inmueble;


class TipoInmuebleService
{


    public function getTipoInmueble()
    {
        //$tipo_impuesto = Tipo_inmueble::all();
        $tipo_impuesto = Tipo_inmueble::all();
        
        return $tipo_impuesto;
    }
}
