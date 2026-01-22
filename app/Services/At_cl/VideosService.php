<?php
namespace App\Services\At_cl;

use App\Models\At_cl\Video;

class VideosService
{
    /**
     * Obtiene todos los videos asociados a una propiedad.
     *
     * @param int|string $propiedadId  ID de la propiedad.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerVideos($propiedadId)
    {
        return Video::where('propiedad_id', $propiedadId)->get();
    }

    /**
     * Crea un nuevo registro de video asociado a una propiedad.
     *
     * @param array $videoData  Datos validados del video.
     * @return \App\Models\At_cl\Video
     */
    public function crearVideos(array $videoData)
    {
        return Video::create($videoData);
    }
}