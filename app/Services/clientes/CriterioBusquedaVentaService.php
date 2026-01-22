<?php

namespace App\Services\clientes;

use App\Models\cliente\CriterioBusquedaVenta;
use Illuminate\Support\Facades\Log;

class CriterioBusquedaVentaService
{
    public function guardarcriterioBusquedaVenta(array $data)
    {   
        try {
            return CriterioBusquedaVenta::create($data); // Guarda los datos en la base de datos
        } catch (\Exception $e) {
            // Podés loguear el error si querés: \Log::error($e);
            return null;
        }
    }

    public static function getCriteriosExistentesPorIDCliente($idCliente){
        return CriterioBusquedaVenta::where('id_cliente', $idCliente)
            ->where('estado_criterio_venta', 'Activo')
            ->get();
    }
}
