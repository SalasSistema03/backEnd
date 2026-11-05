<?php

namespace App\Services\Dpto_Tecnico;

use App\Models\Dpto\InventarioVideo;
use App\Models\proceso\Proceso_propiedad;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

/**
 * Videos del inventario. Usa la misma estructura de rutas que las fotos:
 * /inventario/{propiedad_id}/{año}/{archivo}
 */
class InventarioVideoService
{
    /**
     * Guarda el video en la carpeta de la propiedad y registra su URL.
     */
    public function guardar(Proceso_propiedad $proceso, UploadedFile $file, ?string $notes = null): InventarioVideo
    {
        $carpeta = InventarioRutas::carpeta($proceso);

        if (!File::exists($carpeta)) {
            File::makeDirectory($carpeta, 0777, true);
        }

        $anio = InventarioRutas::anio($proceso);
        $prefijoUrl = "/inventario/{$proceso->id_propiedad}/{$anio}/video_";
        $cantidad = InventarioVideo::where('url', 'like', $prefijoUrl . '%')->count();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $nombreBase = "video_{$proceso->id_propiedad}_{$cantidad}.{$extension}";
        $nombre = InventarioRutas::nombreDisponible($carpeta, $nombreBase);
        $destino = $carpeta . '\\' . $nombre;

        if (!copy($file->getPathname(), $destino)) {
            throw new \RuntimeException("No se pudo guardar el video en {$destino}");
        }

        return InventarioVideo::create([
            'id_proceso_propiedad' => $proceso->id,
            'url' => InventarioRutas::url($proceso, $nombre),
            'notes' => $notes,
        ]);
    }

    public function listar(int $idProcesoPropiedad)
    {
        return InventarioVideo::where('id_proceso_propiedad', $idProcesoPropiedad)
            ->orderBy('id')
            ->get();
    }

    public function eliminar(InventarioVideo $video): void
    {
        $fisica = InventarioRutas::rutaFisicaDesdeUrl($video->url);

        if (File::exists($fisica) && !File::delete($fisica)) {
            throw new \RuntimeException("No se pudo borrar el video {$fisica}");
        }

        $video->delete();
    }
}
