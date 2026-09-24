<?php

namespace App\Http\Controllers\At_cl;

use App\Services\At_cl\ZonaService;
use Illuminate\Support\Facades\Log;

class ZonaController
{
    public function getZonas()
    {
        //Log::info('ZonaController@getZonas called'); // Log the method call
        try {
            $zona = (new ZonaService())->getZonas();
            return response()->json($zona);
        } catch (\Exception $e) {
            // Manejar la excepción y devolver un mensaje de error
            //Log::info('error en zona');
            return response()->json(['error' => 'Error al obtener las zonas: ' . $e->getMessage()], 500);
        }
        /* $zona = (new ZonaService())->getZonas();
        return response()->json($zona); */
    }
}
