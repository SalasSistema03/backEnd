<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Documentacion;


class documentacionService
{
      /**
     * Obtiene todos los documentos asociados a una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerDocumento($documentoId)
    {
        return Documentacion::where('propiedad_id', $documentoId)->get();
    }

    public function crearFoto(array $fotoData)
    {
        return Documentacion::create($fotoData);
    }
}