<?php

namespace App\Services\clientes;

use App\Models\cliente\HistorialCriterioConversacionAlq;

class HistorialCriterioConversacionAlqService
{
    public function buscarPorIdCriterioAlquiler(int $id)
    {
        return HistorialCriterioConversacionAlq::where('id_criterio_alquiler', $id)->get();
    }
}
