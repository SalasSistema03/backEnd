<?php

namespace App\Services\clientes;

use App\Models\At_cl\Zona;

class ZonasService
{
    public function getAllZonas()
    {

        $zonas = Zona::all();
        return $zonas;
    }
}
