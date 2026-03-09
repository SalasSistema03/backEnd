<?php

namespace App\Http\Controllers\At_cl;

use App\Services\At_cl\EstadoVentaService;



class EstadoVentaController
{
    public function getEstadoVenta()
    {
        $estadoVenta = (new EstadoVentaService())->getEstadoVenta();
        return response()->json($estadoVenta);
    }
}
