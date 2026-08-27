<?php

namespace App\Services\clientes;

use Illuminate\Support\Facades\Log;
use App\Models\cliente\CriterioBusquedaAlquiler;
use Carbon\Carbon;

class CriterioBusquedaAlquilerService
{
    public function guardarcriterioBusquedaAlquiler(array $data)
    {
        //Log::info($data);
        //dd('hola');
        try {
            $data['fecha_criterio_alquiler'] = Carbon::now()->format('Y-m-d H:i:s');

            return CriterioBusquedaAlquiler::create($data); // Guarda los datos en la base de datos
        } catch (\Exception $e) {
            // Podés loguear el error si querés: \Log::error($e);
            return null;
        }
    }
}
