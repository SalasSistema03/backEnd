<?php

namespace App\Services\clientes;

use Illuminate\Support\Facades\Log;
use App\Models\cliente\CriterioBusquedaAlquiler;

class CriterioBusquedaAlquilerService
{
    public function guardarcriterioBusqueda(array $data)
    {   
        try {
            Log::info($data);
            return CriterioBusquedaAlquiler::create($data); // Guarda los datos en la base de datos
        } catch (\Exception $e) {
            // Podés loguear el error si querés: \Log::error($e);
            return null;
        }
    }
}
