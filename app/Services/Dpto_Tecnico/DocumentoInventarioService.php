<?php

namespace App\Services\Dpto_Tecnico;

use App\Models\proceso\Documento_inventario;
use App\Models\proceso\Proceso_propiedad;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Documentos Word de inventario. Hay un documento por proceso: al subir uno nuevo
 * se reemplaza el anterior. Rutas en InventarioRutas.
 */
class DocumentoInventarioService
{
    public function listar(int $idProcesoPropiedad)
    {
        return Documento_inventario::with('quien_cargo:id,username')
            ->where('id_proceso_propiedad', $idProcesoPropiedad)
            ->orderBy('id')
            ->get();
    }

    /**
     * Copia el archivo con su nombre original, crea el registro y después borra el/los
     * anteriores del proceso.
     *
     * Si el nombre nuevo coincide con el documento anterior (que se reemplaza), ese
     * archivo se libera antes de copiar, así el nuevo queda con el nombre exacto.
     */
    public function guardar(Proceso_propiedad $proceso, UploadedFile $file, ?int $userId): Documento_inventario
    {
        $carpeta = InventarioRutas::carpeta($proceso);

        if (!File::exists($carpeta)) {
            File::makeDirectory($carpeta, 0777, true);
        }

        $anteriores = Documento_inventario::where('id_proceso_propiedad', $proceso->id)->get();
        $nombreDeseado = InventarioRutas::nombreSeguro($file->getClientOriginalName());

        // Anteriores cuyo archivo ya se liberó: no se les vuelve a borrar el archivo (la ruta
        // ahora apunta al nuevo, porque tiene el mismo nombre).
        $liberados = [];
        foreach ($anteriores as $anterior) {
            if ($this->nombreEnDisco($anterior) === $nombreDeseado) {
                $this->eliminarArchivo($anterior);
                $liberados[] = $anterior->id;
            }
        }

        $nombre = InventarioRutas::nombreDisponible($carpeta, $nombreDeseado);
        $destino = $carpeta . '\\' . $nombre;

        if (!copy($file->getPathname(), $destino)) {
            throw new \RuntimeException("No se pudo guardar el documento en {$destino}");
        }

        $nuevo = Documento_inventario::create([
            'id_proceso_propiedad' => $proceso->id,
            'propiedad_id'         => $proceso->id_propiedad,
            'url'                  => InventarioRutas::url($proceso, $nombre),
            /*  'nombre_original'      => Str::limit(basename($file->getClientOriginalName()), 255, ''), */
            'quien_cargo'          => $userId,
        ]);

        foreach ($anteriores as $anterior) {
            try {
                if (in_array($anterior->id, $liberados, true)) {
                    $anterior->delete();
                } else {
                    $this->eliminar($anterior);
                }
            } catch (\Throwable $e) {
                // El nuevo ya quedó guardado; el anterior queda visible en la lista para borrarlo a mano.
                Log::warning('No se pudo reemplazar documento de inventario anterior: ' . $e->getMessage(), ['id' => $anterior->id]);
            }
        }

        return $nuevo->fresh('quien_cargo:id,username');
    }

    /**
     * Borra el archivo físico y después el registro. Si el archivo no se puede
     * borrar, lanza excepción y el registro queda para reintentar.
     */
    public function eliminar(Documento_inventario $documento): void
    {
        $this->eliminarArchivo($documento);

        $documento->delete();
    }

    private function eliminarArchivo(Documento_inventario $documento): void
    {
        $fisica = InventarioRutas::rutaFisicaDesdeUrl($documento->url);

        if (File::exists($fisica) && !File::delete($fisica)) {
            throw new \RuntimeException("No se pudo borrar el archivo {$fisica}");
        }
    }

    /** Nombre del archivo tal como está en disco (la última parte de la URL, decodificada). */
    private function nombreEnDisco(Documento_inventario $documento): string
    {
        return basename(rawurldecode($documento->url));
    }
}
