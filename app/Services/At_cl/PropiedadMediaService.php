<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Foto;
use App\Models\At_cl\Video;
use App\Models\At_cl\Documentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de gestión de archivos multimedia asociados a propiedades
 *
 * Esta clase se encarga de procesar archivos enviados desde formularios,
 * almacenarlos físicamente en rutas de red según su tipo (imágenes,
 * documentos o videos) y registrar su información en la base de datos.
 *
 * @category   Services
 * @package    App\Services\At_cl
 * @author     Sistema
 * @since      Class available since Release 1.0.0
 */
class PropiedadMediaService
{
    /**
     * Procesa y guarda archivos multimedia enviados desde un request
     *
     * Este método detecta archivos cargados en el request, determina su tipo
     * por extensión y delega el guardado físico y lógico al método
     * correspondiente (imagen, documento o video).
     *
     * @param Request $request request HTTP con archivos adjuntos
     * @param int $propiedadId identificador de la propiedad asociada
     * @return void
     */
    public function subirDesdeRequest(Request $request, int $propiedadId): void
    {
        try {
            DB::beginTransaction();

            if (!$request->hasFile('images') && !$request->hasFile('videos') && !$request->hasFile('pdfs')) {
                return;
            }

            $idFolder = 'propiedad_' . $propiedadId;

            $paths = [
                'imagenes'   => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}",
                'documentos' => "\\\\10.10.10.152\\compartida\\DOCUMENTACION\\{$idFolder}",
                'videos'     => "\\\\10.10.10.153\\compartida\\VIDEOS\\{$idFolder}",
            ];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $descripcion = $request->input("images_comments.{$index}");
                    $extension = strtolower($file->getClientOriginalExtension());
                    $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                    $this->guardarImagen($file, $paths['imagenes'], $fileName, $propiedadId, $idFolder, $descripcion);
                }
            }

            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $index => $file) {
                    $descripcion = $request->input("videos_comments.{$index}");
                    $extension = strtolower($file->getClientOriginalExtension());
                    $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                    $this->guardarVideo($file, $paths['videos'], $fileName, $propiedadId, $idFolder, $descripcion);
                }
            }

            if ($request->hasFile('pdfs')) {
                foreach ($request->file('pdfs') as $index => $file) {
                    $descripcion = $request->input("pdfs_comments.{$index}");
                    $extension = strtolower($file->getClientOriginalExtension());
                    $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                    $this->guardarDocumento($file, $paths['documentos'], $fileName, $propiedadId, $idFolder, $descripcion);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al guardar archivos multimedia: ' . $e->getMessage());
        }
    }

    /**
     * Guarda un documento PDF asociado a una propiedad
     *
     * Almacena el archivo físicamente en la ruta correspondiente y registra
     * su información en la tabla de documentación.
     *
     * @param mixed $file archivo recibido desde el request
     * @param string $path ruta física donde se almacenará
     * @param string $fileName nombre del archivo
     * @param int $propiedadId identificador de la propiedad
     * @param string $folder nombre de la carpeta lógica
     * @param string|null $descripcion descripción opcional del archivo
     * @return void
     */
    private function guardarDocumento($file, string $path, string $fileName, int $propiedadId, string $folder, ?string $descripcion): void
    {
        $this->guardarArchivoFisico($file, $path, $fileName);

        Documentacion::create([
            'propiedad_id' => $propiedadId,
            'url'          => "/documentos/{$folder}/{$fileName}",
            'notes'        => $descripcion,
        ]);
    }

    /**
     * Guarda una imagen asociada a una propiedad
     *
     * Almacena el archivo físicamente y crea el registro correspondiente
     * en la tabla de fotos.
     *
     * @param mixed $file archivo recibido desde el request
     * @param string $path ruta física donde se almacenará
     * @param string $fileName nombre del archivo
     * @param int $propiedadId identificador de la propiedad
     * @param string $folder nombre de la carpeta lógica
     * @param string|null $descripcion descripción opcional de la imagen
     * @return void
     */
    private function guardarImagen($file, string $path, string $fileName, int $propiedadId, string $folder, ?string $descripcion): void
    {
        $this->guardarArchivoFisico($file, $path, $fileName);

        Foto::create([
            'propiedad_id' => $propiedadId ?? null,
            'url'          => "/imagenes/{$folder}/{$fileName}",
            'notes'        => $descripcion ?? null,
            'archivado'    => 0,
        ]);
    }

    /**
     * Guarda un video asociado a una propiedad
     *
     * Almacena el archivo físicamente y registra su referencia
     * en la tabla de videos.
     *
     * @param mixed $file archivo recibido desde el request
     * @param string $path ruta física donde se almacenará
     * @param string $fileName nombre del archivo
     * @param int $propiedadId identificador de la propiedad
     * @param string $folder nombre de la carpeta lógica
     * @param string|null $descripcion descripción opcional del video
     * @return void
     */
    private function guardarVideo($file, string $path, string $fileName, int $propiedadId, string $folder, ?string $descripcion): void
    {
        $this->guardarArchivoFisico($file, $path, $fileName);

        Video::create([
            'propiedad_id' => $propiedadId,
            'url'          => "/videos/{$folder}/{$fileName}",
            'notes'        => $descripcion,
            'archivado'    => 0,
        ]);
    }

    /**
     * Guarda físicamente un archivo en el sistema de archivos
     *
     * Crea la carpeta destino si no existe, copia el archivo y elimina
     * el temporal generado por el request.
     *
     * @param mixed $file archivo recibido desde el request
     * @param string $path ruta física de destino
     * @param string $fileName nombre final del archivo
     * @return void
     */
    private function guardarArchivoFisico($file, string $path, string $fileName): void
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }

        $destination = $path . '\\' . $fileName;

        copy($file->getPathname(), $destination);
        unlink($file->getPathname());
    }

    public function modificarFoto($fotos_modificadas)
    {
        try {
            DB::beginTransaction();

            foreach ($fotos_modificadas as $foto) {
                $fotoModel = Foto::find($foto['id']);
                if ($fotoModel) {
                    $fotoModel->update([
                        'orden'     => $foto['orden'] ?? null,
                        'notes'     => $foto['notes'] ?? null,
                        'archivado' => $foto['archivado'] ?? null,
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al modificar fotos: ' . $e->getMessage());
        }
    }

    public function modificarDocumento($documentos_modificados)
    {
        try {
            DB::beginTransaction();

            foreach ($documentos_modificados as $documento) {
                $documentoModel = Documentacion::find($documento['id']);
                if ($documentoModel) {
                    $documentoModel->update([
                        'notes'      => $documento['notes'] ?? null,
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al modificar documentos: ' . $e->getMessage());
        }
    }

    public function modificarVideo($videos_modificados)
    {
        try {
            DB::beginTransaction();

            foreach ($videos_modificados as $video) {
                $videoModel = Video::find($video['id']);
                if ($videoModel) {
                    $videoModel->update([
                        'notes'      => $video['notes'] ?? null,
                        'updated_at' => now(),
                        'archivado' => $video['archivado'] ?? null,
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al modificar videos: ' . $e->getMessage());
        }
    }

    public function eliminarFoto($fotos_eliminadas)
    {
        try {
            DB::beginTransaction();

            foreach ($fotos_eliminadas as $foto) {
                $fotoId = is_array($foto) ? $foto['id'] : $foto;

                $fotoModel = Foto::find($fotoId);
                if ($fotoModel) {
                    $this->eliminarArchivoFisico($fotoModel->url);
                    $fotoModel->delete();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar fotos: ' . $e->getMessage());
        }
    }
    public function eliminarDocumento($documentos_eliminados)
    {
        try {
            DB::beginTransaction();

            foreach ($documentos_eliminados as $documento) {
                $documentoId = is_array($documento) ? $documento['id'] : $documento;

                $documentoModel = Documentacion::find($documentoId);
                if ($documentoModel) {
                    $this->eliminarArchivoFisico($documentoModel->url);
                    $documentoModel->delete();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar documentos: ' . $e->getMessage());
        }
    }

    public function eliminarVideo($videos_eliminados)
    {
        try {
            DB::beginTransaction();

            foreach ($videos_eliminados as $video) {
                $videoId = is_array($video) ? $video['id'] : $video;

                $videoModel = Video::find($videoId);
                if ($videoModel) {
                    $this->eliminarArchivoFisico($videoModel->url);
                    $videoModel->delete();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al eliminar videos: ' . $e->getMessage());
        }
    }
    /**
     * Elimina físicamente un archivo del sistema de archivos
     *
     * @param string $url URL relativa guardada en la base de datos
     * @return void
     */
    private function eliminarArchivoFisico(string $url): void
    {
        try {
            $partes = explode('/', $url);
            if (count($partes) >= 3 && $partes[1] === 'imagenes') {
                $folder = $partes[2];
                $nombreArchivo = $partes[3] ?? '';

                $rutaFisica = "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$folder}\\{$nombreArchivo}";

                if (File::exists($rutaFisica)) {
                    File::delete($rutaFisica);
                }
            } else if (count($partes) >= 3 && $partes[1] === 'documentos') {
                $folder = $partes[2];
                $nombreArchivo = $partes[3] ?? '';

                $rutaFisica = "\\\\10.10.10.152\\compartida\\DOCUMENTACION\\{$folder}\\{$nombreArchivo}";

                if (File::exists($rutaFisica)) {
                    File::delete($rutaFisica);
                }
            }
        } catch (\Exception $e) {
            // Silenciar errores de eliminación de archivos físicos
        }
    }

    /**
     * Sube archivos multimedia desde una actualización de propiedad
     *
     * Este método maneja la carga de nuevos archivos (fotos, documentos, videos)
     * durante la actualización de una propiedad existente.
     *
     * @param Request $request Request con los archivos y datos
     * @param int $propiedadId ID de la propiedad
     * @return void
     */
    public function subirdesdeUpdate(Request $request, $propiedadId)
    {
        try {
            DB::beginTransaction();

            $idFolder = 'propiedad_' . $propiedadId;
            $paths = [
                'imagenes' => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}\\",
                'videos' => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}\\",
                'pdfs' => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}\\"
            ];
        //paths de local
       /*  $paths = [
            'imagenes'   => "\\\\10.10.10.48\\Users\\SISTEMA\\Pictures\\PROPIEDADES\\{$idFolder}",
            'documentos' => "\\\\10.10.10.48\\Users\\SISTEMA\\Pictures\\DOCUMENTACION\\{$idFolder}",
            'videos'     => "\\\\10.10.10.48\\Users\\SISTEMA\\Pictures\\VIDEOS\\{$idFolder}",
        ]; */
        //Log::info('entro y esta antes del if');
            if ($request->has('fotos_nuevas_data') && $request->hasFile('fotos_nuevas')) {
                $fotosNuevas = $request->file('fotos_nuevas');
                $fotosData = json_decode($request->fotos_nuevas_data, true);

                if (!$fotosData) {
                    return;
                }

                foreach ($fotosData as $index => $foto) {
                    if (!isset($fotosNuevas[$index])) {
                        continue;
                    }

                    $file = $fotosNuevas[$index];
                    $descripcion = $foto['comentario'] ?? '';

                    // Obtener extensión desde el mimeType
                    $mimeType = $file->getMimeType();
                    $extension = $this->getExtensionFromMimeType($mimeType);

                    if (!$extension) {
                        $extension = strtolower($file->getClientOriginalExtension());
                    }

                    $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                    $this->guardarImagen($file, $paths['imagenes'], $fileName, $propiedadId, $idFolder, $descripcion);
                }
            }
            // Procesar nuevos documentos
            else if ($request->has('documentos_nuevos_data') && $request->hasFile('documentos_nuevos')) {
                $documentosNuevos = $request->file('documentos_nuevos');
                $DocumentosData = json_decode($request->documentos_nuevos_data, true);

                if (!$DocumentosData) {
                    return;
                }

                foreach ($DocumentosData as $index => $documento) {
                    if (!isset($documentosNuevos[$index])) {
                        continue;
                    }

                    $file = $documentosNuevos[$index];
                    $descripcion = $documento['comentario'] ?? '';

                    // Obtener extensión desde el mimeType
                    $mimeType = $file->getMimeType();
                    $extension = $this->getExtensionFromMimeType($mimeType);

                    if (!$extension) {
                        $extension = strtolower($file->getClientOriginalExtension());
                    }

                    $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                    $this->guardarDocumento($file, $paths['documentos'], $fileName, $propiedadId, $idFolder, $descripcion);
                }
            }
            // Procesar nuevos videos
            else if ($request->has('videos_nuevos_data') && $request->hasFile('videos_nuevos')) {
                $videosNuevos = $request->file('videos_nuevos');
                $VideosData = json_decode($request->videos_nuevos_data, true);

                if (!$VideosData) {
                    return;
                }

                foreach ($VideosData as $index => $video) {
                    if (!isset($videosNuevos[$index])) {
                        continue;
                    }

                    $file = $videosNuevos[$index];
                    $descripcion = $video['comentario'] ?? '';

                    $mimeType = $file->getMimeType();
                    $extension = $this->getExtensionFromMimeType($mimeType);

                    if (!$extension) {
                        $extension = strtolower($file->getClientOriginalExtension());
                    }

                    $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                    $this->guardarVideo($file, $paths['videos'], $fileName, $propiedadId, $idFolder, $descripcion);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al subir archivos multimedia: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la extensión de archivo a partir del MIME type
     *
     * @param string $mimeType MIME type del archivo
     * @return string|null Extensión correspondiente o null si no se reconoce
     */
    private function getExtensionFromMimeType(string $mimeType): ?string
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/svg+xml' => 'svg',
            'image/tiff' => 'tiff',
            'image/x-icon' => 'ico',
            'application/pdf' => 'pdf',
        ];

        return $mimeToExt[$mimeType] ?? null;
    }
}
