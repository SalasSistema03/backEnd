<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Foto;

class FotosService
{
    /**
     * Obtiene las fotos asociadas a una propiedad.
     *
     * El ordenamiento funciona así:
     * - Primero muestra las fotos que SÍ tienen un campo "orden" definido.
     * - Luego muestra las que tienen "orden" NULL.
     * - Dentro de las ordenadas, respeta el orden ascendente.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerFotos($propiedadId)
    {
        return Foto::where('propiedad_id', $propiedadId)
            ->orderByRaw('orden IS NULL')
            ->orderBy('orden', 'asc')
            ->get();
    }

    /**
     * Crea un nuevo registro de foto asociado a una propiedad.
     *
     * @param array $fotoData  Datos validados de la foto.
     * @return \App\Models\At_cl\Foto
     */
    public function crearFoto(array $fotoData)
    {
        return Foto::create($fotoData);
    }
}
