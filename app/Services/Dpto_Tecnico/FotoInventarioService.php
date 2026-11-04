<?php

namespace App\Services\Dpto_Tecnico;

use App\Models\proceso\Foto_inventario;
use App\Models\proceso\Proceso_propiedad;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Fotos de inventario del Departamento Técnico. Rutas en InventarioRutas.
 */
class FotoInventarioService
{
    public function listar(int $idProcesoPropiedad)
    {
        return Foto_inventario::with('quien_cargo:id,username')
            ->where('id_proceso_propiedad', $idProcesoPropiedad)
            ->orderBy('id')
            ->get();
    }

    /**
     * Guarda cada archivo en la carpeta del proceso y crea su registro.
     *
     * @param UploadedFile[] $archivos
     * @param string[]       $comentarios mismo orden que $archivos
     */
    public function guardar(Proceso_propiedad $proceso, array $archivos, array $comentarios, ?int $userId): array
    {
        $carpeta = InventarioRutas::carpeta($proceso);
        //Log::info($carpeta);
        //dd('hola');

        if (!File::exists($carpeta)) {
            File::makeDirectory($carpeta, 0777, true);
        }

        // Cantidad de fotos ya cargadas en este proceso para continuar la correlatividad
        $contadorActual = Foto_inventario::where('id_proceso_propiedad', $proceso->id)->count();

        $creadas = [];
        foreach ($archivos as $index => $file) {
            $numeroFoto = $contadorActual + $index;
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $nombreBase = "propiedad_{$proceso->id_propiedad}_{$numeroFoto}.{$extension}";

            $nombre = InventarioRutas::nombreDisponible($carpeta, $nombreBase);
            $destino = $carpeta . '\\' . $nombre;

            if (!copy($file->getPathname(), $destino)) {
                throw new \RuntimeException("No se pudo guardar la foto en {$destino}");
            }

            $creadas[] = Foto_inventario::create([
                'id_proceso_propiedad' => $proceso->id,
                'propiedad_id'         => $proceso->id_propiedad,
                'url'                  => InventarioRutas::url($proceso, $nombre),
                'notes'                => $comentarios[$index] ?? null,
                'quien_cargo'          => $userId,
            ]);
        }

        return $creadas;
    }

    public function actualizarComentario(Foto_inventario $foto, ?string $notes): Foto_inventario
    {
        $foto->update(['notes' => $notes]);

        return $foto->fresh('quien_cargo:id,username');
    }

    /**
     * Borra el archivo físico y después el registro. Si el archivo no se puede
     * borrar, lanza excepción y el registro queda para reintentar.
     */
    public function eliminar(Foto_inventario $foto): void
    {
        $fisica = InventarioRutas::rutaFisicaDesdeUrl($foto->url);

        if (File::exists($fisica) && !File::delete($fisica)) {
            throw new \RuntimeException("No se pudo borrar el archivo {$fisica}");
        }

        $foto->delete();
    }
}
