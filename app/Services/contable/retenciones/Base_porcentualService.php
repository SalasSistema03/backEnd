<?php

namespace App\Services\contable\retenciones;

use App\Models\Contable\retenciones\Base_porcentual;


class Base_porcentualService
{
   



    public function devolverBasePorcentual()
    {
        $bases_porcentuales = Base_porcentual::all();
        return response()->json($bases_porcentuales);
    }

}