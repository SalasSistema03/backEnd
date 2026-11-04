<?php

namespace App\Services\Dpto_Tecnico;

use App\Models\proceso\Proceso_propiedad;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Rutas de los archivos de inventario (fotos y documentos).
 *
 * Carpeta física: {ruta}\{propiedad_id}\{año_reserva}\{id_proceso_propiedad}\{archivo}
 * URL guardada:   /inventario/{propiedad_id}/{año_reserva}/{id_proceso_propiedad}/{archivo}
 */
class InventarioRutas
{
    /**
     * Año de la carpeta: año de la reserva. Si no hay fecha de reserva, año de
     * carga del inventario actual; si tampoco existe, año actual.
     */
    public static function anio(Proceso_propiedad $proceso): int
    {
        $fecha = $proceso->fecha_reserva ?? $proceso->historialEstadoDpto?->fecha_carga;

        return $fecha ? Carbon::parse($fecha)->year : now()->year;
    }

    public static function carpeta(Proceso_propiedad $proceso): string
    {
        return config('inventario.fotos_ruta_fisica') . '\\' . $proceso->id_propiedad . '\\'
            . self::anio($proceso);
    }

    public static function url(Proceso_propiedad $proceso, string $archivo): string
    {
        return config('inventario.fotos_url_base') . "/{$proceso->id_propiedad}/"
            . self::anio($proceso) . "/" . rawurlencode($archivo);
    }

    /** Convierte la URL pública guardada en la ruta física dentro del recurso compartido. */
    public static function rutaFisicaDesdeUrl(string $url): string
    {
        $base = config('inventario.fotos_url_base');
        $relativa = str_replace('/', '\\', rawurldecode(substr($url, strlen($base))));

        return config('inventario.fotos_ruta_fisica') . $relativa;
    }

    /**
     * Nombre de archivo seguro para Windows, conservando el nombre original.
     * Quita rutas, caracteres prohibidos en NTFS y puntos/espacios finales.
     */
    public static function nombreSeguro(string $nombreOriginal): string
    {
        $nombre = basename(str_replace('\\', '/', $nombreOriginal));
        $nombre = preg_replace('/[\x00-\x1F\x7F<>:"\/\\\\|?*]+/', '_', $nombre);
        $nombre = trim($nombre, ' .');

        if ($nombre === '') {
            return 'archivo';
        }

        // Se recorta solo el nombre base, para no perder la extensión.
        $extension = pathinfo($nombre, PATHINFO_EXTENSION);
        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $maxBase = 150 - ($extension !== '' ? strlen($extension) + 1 : 0);
        $base = mb_strcut($base, 0, $maxBase, 'UTF-8');

        return $extension !== '' ? "{$base}.{$extension}" : $base;
    }

    /**
     * Si el nombre ya existe en la carpeta, agrega " (n)" antes de la extensión:
     * "IMG_01.jpg" -> "IMG_01 (1).jpg".
     */
    public static function nombreDisponible(string $carpeta, string $nombre): string
    {
        $extension = pathinfo($nombre, PATHINFO_EXTENSION);
        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $sufijoExt = $extension !== '' ? ".{$extension}" : '';

        $candidato = $nombre;
        $n = 1;
        while (File::exists($carpeta . '\\' . $candidato)) {
            $candidato = "{$base} ({$n}){$sufijoExt}";
            $n++;
        }

        return $candidato;
    }
}
