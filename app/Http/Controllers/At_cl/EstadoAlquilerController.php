<?php

namespace App\Http\Controllers\At_cl;

use App\Services\At_cl\EstadoAlquilerService;



class EstadoAlquilerController
{
    public function getEstadoAlquiler()
    {
        $estadoAlquiler = (new EstadoAlquilerService())->getEstadoAlquiler();
        return response()->json($estadoAlquiler);
    }
}
