<?php

namespace App\Services\clientes;

use App\Models\cliente\ConsultaPropVenta;
use Illuminate\Support\Facades\Log;
class ConsultaPropVentaService
{

    public function guardarConsultaPropVenta(array $data)
    {
        
        try {
            Log::info($data);
            return ConsultaPropVenta::create($data);
        } catch (\Exception $e) {
            // Podés loguear el error si querés: \Log::error($e->getMessage());
            return null;
        }
    }
}
