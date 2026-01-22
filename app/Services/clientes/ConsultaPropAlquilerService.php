<?php

namespace App\Services\clientes;

use App\Models\cliente\ConsultaPropAlquiler;

class ConsultaPropAlquilerService
{
    
    public function guardarConsultaPropAlquiler(array $data)
    {
        try {
            return ConsultaPropAlquiler::create($data);
        } catch (\Exception $e) {
            // Podés loguear el error si querés: \Log::error($e->getMessage());
            return null;
        }
    }
}
