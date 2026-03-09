<?php

namespace App\Http\Controllers\At_cl;

use App\Services\At_cl\ProvinciaService;



class ProvinciaController
{
    public function getProvincias()
    {
        $provincia = (new ProvinciaService())->getProvincias();
        return response()->json($provincia);
    }
}
