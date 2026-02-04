<?php

namespace App\Http\Controllers\At_cl;

use App\Services\At_cl\ZonaService;



class ZonaController
{
    public function getZonas()
    {
        $zona = (new ZonaService())->getZonas();
        return response()->json($zona);
    }
}
