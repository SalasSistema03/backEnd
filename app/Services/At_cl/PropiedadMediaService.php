<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Foto;
use App\Models\At_cl\Video;
use App\Models\At_cl\Documentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\ElseIf_;

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
     * @param Request $request     request HTTP con archivos adjuntos
     * @param int     $propiedadId identificador de la propiedad asociada
     *
     * @return void
     * @access public
     */
    public function subirDesdeRequest(Request $request, int $propiedadId): void
    {
        /* Verificar si existen archivos cargados en cualquier formato */
        if (!$request->hasFile('images') && !$request->hasFile('videos') && !$request->hasFile('pdfs')) {
            return;
        }

        /* Nombre de carpeta única por propiedad */
        $idFolder = 'propiedad_' . $propiedadId;

        /* Rutas físicas de almacenamiento por tipo de archivo */
        $paths = [
            'imagenes'   => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}",
            'documentos' => "\\\\10.10.10.152\\compartida\\DOCUMENTACION\\{$idFolder}",
            'videos'     => "\\\\10.10.10.153\\compartida\\VIDEOS\\{$idFolder}",
        ];

        /* Procesar imágenes */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $descripcion = $request->input("images_comments.{$index}");
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                $this->guardarImagen($file, $paths['imagenes'], $fileName, $propiedadId, $idFolder, $descripcion);
            }
        }

        /* Procesar videos */
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $index => $file) {
                $descripcion = $request->input("videos_comments.{$index}");
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                $this->guardarVideo($file, $paths['videos'], $fileName, $propiedadId, $idFolder, $descripcion);
            }
        }

        /* Procesar documentos PDFs */
        if ($request->hasFile('pdfs')) {
            foreach ($request->file('pdfs') as $index => $file) {
                $descripcion = $request->input("pdfs_comments.{$index}");
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                $this->guardarDocumento($file, $paths['documentos'], $fileName, $propiedadId, $idFolder, $descripcion);
            }
        }
    }

    /**
     * Guarda un documento PDF asociado a una propiedad
     *
     * Almacena el archivo físicamente en la ruta correspondiente y registra
     * su información en la tabla de documentación.
     *
     * @param mixed       $file        archivo recibido desde el request
     * @param string      $path        ruta física donde se almacenará
     * @param string      $fileName    nombre del archivo
     * @param int         $propiedadId identificador de la propiedad
     * @param string      $folder      nombre de la carpeta lógica
     * @param string|null $descripcion descripción opcional del archivo
     *
     * @return void
     * @access private
     */
    private function guardarDocumento($file, string $path, string $fileName, int $propiedadId, string $folder, ?string $descripcion): void
    {
        /* Guardar archivo en el sistema */
        $this->guardarArchivoFisico($file, $path, $fileName);

        /* Registrar documento en base de datos */
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
     * @param mixed       $file        archivo recibido desde el request
     * @param string      $path        ruta física donde se almacenará
     * @param string      $fileName    nombre del archivo
     * @param int         $propiedadId identificador de la propiedad
     * @param string      $folder      nombre de la carpeta lógica
     * @param string|null $descripcion descripción opcional de la imagen
     *
     * @return void
     * @access private
     */
    private function guardarImagen($file, string $path, string $fileName, int $propiedadId, string $folder, ?string $descripcion): void
    {

        /* Guardar archivo en el sistema */
        $this->guardarArchivoFisico($file, $path, $fileName);

        /* Registrar imagen en base de datos */
        $fotocreada = Foto::create([
            'propiedad_id' => $propiedadId ?? null,
            'url'          => "/imagenes/{$folder}/{$fileName}",
            'notes'        => $descripcion ?? null,
            'archivado'    => 0,
        ]);
        //dd($fotocreada);
    }

    /**
     * Guarda un video asociado a una propiedad
     *
     * Almacena el archivo físicamente y registra su referencia
     * en la tabla de videos.
     *
     * @param mixed       $file        archivo recibido desde el request
     * @param string      $path        ruta física donde se almacenará
     * @param string      $fileName    nombre del archivo
     * @param int         $propiedadId identificador de la propiedad
     * @param string      $folder      nombre de la carpeta lógica
     * @param string|null $descripcion descripción opcional del video
     *
     * @return void
     * @access private
     */
    private function guardarVideo($file, string $path, string $fileName, int $propiedadId, string $folder, ?string $descripcion): void
    {
        /* Guardar archivo en el sistema */
        $this->guardarArchivoFisico($file, $path, $fileName);

        /* Registrar video en base de datos */
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
     * @param mixed  $file     archivo recibido desde el request
     * @param string $path     ruta física de destino
     * @param string $fileName nombre final del archivo
     *
     * @return void
     * @access private
     */
    private function guardarArchivoFisico($file, string $path, string $fileName): void
    {
        /* Crear directorio si no existe */
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true);
        }

        /* Ruta final del archivo */
        $destination = $path . '\\' . $fileName;

        /* Copiar archivo y eliminar temporal */
        copy($file->getPathname(), $destination);
        unlink($file->getPathname());
    }

    public function modificarFoto($fotos_modificadas)
    {
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
    }

    public function modificarDocumento($documentos_modificados)
    {
        foreach ($documentos_modificados as $documento) {
            $documentoModel = Documentacion::find($documento['id']);
            if ($documentoModel) {
                $documentoModel->update([
                    'notes'      => $documento['notes'] ?? null,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function modificarVideo($videos_modificados){
        foreach ($videos_modificados as $video){
            $videoModel = Video::find($video['id']);
            if($videoModel){
                $videoModel->update([
                    'notes'      => $video['notes'] ?? null,
                    'updated_at' => now(),
                    'archivado' => $video['archivado'] ?? null,
                ]);
            }
        }
    }

    public function eliminarFoto($fotos_eliminadas)
    {
        foreach ($fotos_eliminadas as $foto) {
            // $foto puede ser un ID directo o un array con 'id'
            $fotoId = is_array($foto) ? $foto['id'] : $foto;

            $fotoModel = Foto::find($fotoId);
            if ($fotoModel) {
                // Eliminar archivo físico
                $this->eliminarArchivoFisico($fotoModel->url);

                // Eliminar registro de la base de datos
                $fotoModel->delete();
            }
        }
    }
    public function eliminarDocumento($documentos_eliminados)
    {
        foreach ($documentos_eliminados as $documento) {
            $documentoId = is_array($documento) ? $documento['id'] : $documento;

            $documentoModel = Documentacion::find($documentoId);
            if ($documentoModel) {
                $this->eliminarArchivoFisico($documentoModel->url);
                $documentoModel->delete();
            }
        }
    }

    public function eliminarVideo($videos_eliminados){
        foreach ($videos_eliminados as $video){
            $videoId = is_array($video) ? $video['id'] : $video;

            $videoModel = Video::find($videoId);
            if($videoModel) {
                $this->eliminarArchivoFisico($videoModel->url);
                $videoModel->delete();
            }
        }
    }
    /**
     * Elimina físicamente un archivo del sistema de archivos
     *
     * @param string $url URL relativa guardada en la base de datos
     * @return void
     * @access private
     */
    private function eliminarArchivoFisico(string $url): void
    {
        try {
            // Extraer el folder de la URL (ej: /imagenes/propiedad_881/nombre.jpg -> propiedad_881)
            $partes = explode('/', $url);
            if (count($partes) >= 3 && $partes[1] === 'imagenes') {
                $folder = $partes[2]; // propiedad_881
                $nombreArchivo = $partes[3] ?? ''; // nombre.jpg

                // Construir ruta física completa
                $rutaFisica = "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$folder}\\{$nombreArchivo}";

                // Eliminar archivo si existe
                if (File::exists($rutaFisica)) {
                    File::delete($rutaFisica);
                    //Log::info("Archivo eliminado: {$rutaFisica}");
                } else {
                    Log::warning("Archivo no encontrado para eliminar: {$rutaFisica}");
                }
            } else if (count($partes) >= 3 && $partes[1] === 'documentos') {
                //Log::info('ENTRO A LA ELIMINACION DE DOCU,MENTOP');
                $folder = $partes[2]; // propiedad_881
                $nombreArchivo = $partes[3] ?? ''; // nombre.jpg

                // Construir ruta física completa
                $rutaFisica = "\\\\10.10.10.152\\compartida\\DOCUMENTACION\\{$folder}\\{$nombreArchivo}";

                // Eliminar archivo si existe
                if (File::exists($rutaFisica)) {
                    File::delete($rutaFisica);
                    //Log::info("Archivo eliminado: {$rutaFisica}");
                } else {
                    Log::warning("Archivo no encontrado para eliminar: {$rutaFisica}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al eliminar archivo físico: " . $e->getMessage());
        }
    }

    public function subirdesdeUpdate(Request $request, $propiedadId)
    {

        $idFolder = 'propiedad_' . $propiedadId;
        //paths de main
        /* $paths = [
            'imagenes' => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}\\",
            'videos' => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}\\",
            'pdfs' => "\\\\10.10.10.151\\compartida\\PROPIEDADES\\{$idFolder}\\"
        ]; */
        //paths de local
        $paths = [
            'imagenes'   => "\\\\10.10.10.48\\Users\\SISTEMA\\Pictures\\PROPIEDADES\\{$idFolder}",
            'documentos' => "\\\\10.10.10.48\\Users\\SISTEMA\\Pictures\\DOCUMENTACION\\{$idFolder}",
            'videos'     => "\\\\10.10.10.48\\Users\\SISTEMA\\Pictures\\VIDEOS\\{$idFolder}",
        ];
        Log::info('entro y esta antes del if');
        if ($request->has('fotos_nuevas_data') && $request->hasFile('fotos_nuevas')) {
            $fotosNuevas = $request->file('fotos_nuevas');
            //Log::info('despues del if');

            // Decodificar el JSON de fotos_nuevas_data
            $fotosData = json_decode($request->fotos_nuevas_data, true);

            if (!$fotosData) {
                Log::error('Error al decodificar fotos_nuevas_data');
                return;
            }

            foreach ($fotosData as $index => $foto) {
                // Verificar que exista el archivo correspondiente
                if (!isset($fotosNuevas[$index])) {
                    Log::warning("No se encontró el archivo para el índice {$index}");
                    continue;
                }

                $file = $fotosNuevas[$index];
                $descripcion = $foto['comentario'] ?? '';

                // Obtener extensión desde el mimeType
                $mimeType = $file->getMimeType();
                $extension = $this->getExtensionFromMimeType($mimeType);

                if (!$extension) {
                    // Fallback: obtener extensión del nombre original
                    $extension = strtolower($file->getClientOriginalExtension());
                }

                $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                Log::info('esta por entrar a guardar imagen');
                // Guardar la foto
                $this->guardarImagen($file, $paths['imagenes'], $fileName, $propiedadId, $idFolder, $descripcion);

                Log::info("Foto procesada: {$fileName} - Extensión: {$extension} - MIME: {$mimeType}");
            }
        } else if ($request->has('documentos_nuevos_data') && $request->hasFile('documentos_nuevos')) {
            $documentosNuevos = $request->file('documentos_nuevos');

            // Decodificar el JSON de fotos_nuevas_data
            $DocumentosData = json_decode($request->documentos_nuevos_data, true);

            if (!$DocumentosData) {
                Log::error('Error al decodificar documentos_nuevos_data');
                return ;
            }

            foreach ($DocumentosData as $index => $documento) {
                // Verificar que exista el archivo correspondiente

                if (!isset($documentosNuevos[$index])) {
                    Log::warning("No se encontró el archivo para el índice {$index}");
                    continue;
                }

                $file = $documentosNuevos[$index];
                $descripcion = $documento['comentario'] ?? '';

                // Obtener extensión desde el mimeType
                $mimeType = $file->getMimeType();
                $extension = $this->getExtensionFromMimeType($mimeType);

                if (!$extension) {
                    // Fallback: obtener extensión del nombre original
                    $extension = strtolower($file->getClientOriginalExtension());
                }

                $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                Log::info('esta por entrar a guardar pdf');
                // Guardar la foto
                $this->guardarDocumento($file, $paths['documentos'], $fileName, $propiedadId, $idFolder, $descripcion);

                Log::info("Foto procesada: {$fileName} - Extensión: {$extension} - MIME: {$mimeType}");
            }
        }else if($request->has('videos_nuevos_data') && $request->hasFile('videos_nuevos')){
             $videosNuevos = $request->file('videos_nuevos');
             // Decodificar el JSON de fotos_nuevas_data
            $VideosData = json_decode($request->videos_nuevos_data, true);

            if (!$VideosData) {
                Log::error('Error al decodificar videos_nuevos_data');
                return ;
            }
            foreach ($VideosData as $index => $video) {
                // Verificar que exista el archivo correspondiente

                if (!isset($videosNuevos[$index])) {
                    Log::warning("No se encontró el archivo para el índice {$index}");
                    continue;
                }

                $file = $videosNuevos[$index];
                $descripcion = $video['comentario'] ?? '';

                // Obtener extensión desde el mimeType
                $mimeType = $file->getMimeType();
                $extension = $this->getExtensionFromMimeType($mimeType);

                if (!$extension) {
                    // Fallback: obtener extensión del nombre original
                    $extension = strtolower($file->getClientOriginalExtension());
                }

                $fileName = "propiedad_{$propiedadId}_" . time() . "{$index}.{$extension}";

                //Log::info('esta por entrar a guardar pdf');
                // Guardar la foto
                $this->guardarVideo($file, $paths['videos'], $fileName, $propiedadId, $idFolder, $descripcion);

                Log::info("Foto procesada: {$fileName} - Extensión: {$extension} - MIME: {$mimeType}");
            }
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
