<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Propiedades_padron;
use Illuminate\Support\Facades\DB;

class Propiedades_padronService
{
    /**
     * Obtiene todos los propietarios asociados a una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerPropietarios($propiedadId)
    {
        return DB::table('propiedades_padron')
            ->join('padron', 'propiedades_padron.padron_id', '=', 'padron.id')
            ->where('propiedades_padron.propiedad_id', $propiedadId)
            ->select(
                'padron.nombre',
                'padron.apellido',
                'propiedades_padron.baja',
                'propiedades_padron.fecha_baja',
                'propiedades_padron.observaciones as notes',
                'propiedades_padron.padron_id',
                'propiedades_padron.observaciones_baja'
            )
            ->get();
    }
}
