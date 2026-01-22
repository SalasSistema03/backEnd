<?php
namespace App\Services\At_cl;

use App\Models\At_cl\HistorialFechas;

class HistorialFechasService
{
    /**
     * Obtiene el historial de fechas asociado a una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerHistorialFecha($propiedadId)
    {
        return HistorialFechas::where('propiedad_id', $propiedadId)->first();
    }

    /**
     * Crea un nuevo registro de historial de fechas asociado a una propiedad.
     *
     * @param array $historialData  Datos validados del historial de fechas.
     * @return \App\Models\At_cl\HistorialFechas
     */
    public function crearHistorialFecha(array $historialData)
    {
        return HistorialFechas::create($historialData);
    }
}