<?php

namespace App\Http\Controllers\sys;


use App\Models\sys\Propiedades_sys;
use App\Models\sys\Contratos_Cabecera_sys;
use Illuminate\Http\Request;

class PropiedadesController
{
    public function index()
    {
        /* $propiedades = Propiedades::all();
        $contratos_cabecera = Contratos_cabecera::all(); */

        // Realizar INNER JOIN entre propiedades y contratos_cabecera usando id_casa
        /* $propiedades = Propiedades::join('contratos_cabecera', 'propiedades.id_casa', '=', 'contratos_cabecera.id_casa')
                                    ->select('propiedades.*', 'contratos_cabecera.*') // Selecciona todas las columnas de ambas tablas
                                    ->get();
         */

        $propiedades = Propiedades_sys::join('contratos_cabecera', 'propiedades.id_casa', '=', 'contratos_cabecera.id_casa')
            ->whereRaw('contratos_cabecera.rescicion > CURDATE()')  // Usando CURDATE() para obtener la fecha actual
            ->select('propiedades.*', 'contratos_cabecera.*') // Seleccionar todas las columnas de ambas tablas
            -> orderBy('contratos_cabecera.rescicion', 'asc')
            ->get();

        return view('sys.propiedades', compact('propiedades'));
    }
}
