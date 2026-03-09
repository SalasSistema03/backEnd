<?php

namespace App\Http\Controllers\At_cl;

use App\Services\At_cl\EstadoGeneralService;



class EstadoGeneralController
{
    public function getEstadoGeneral()
    {
        $estadoGeneral = (new EstadoGeneralService())->getEstadoGeneral();
        return response()->json($estadoGeneral);
    }
}
