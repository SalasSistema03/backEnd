<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Foto;
use App\Models\At_cl\Video;
use App\Models\At_cl\Documentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
    private function guardarDocumento(
        $file,
        string $path,
        string $fileName,
        int $propiedadId,
        string $folder,
        ?string $descripcion
    ): void {
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
    private function guardarImagen(
        $file,
        string $path,
        string $fileName,
        int $propiedadId,
        string $folder,
        ?string $descripcion
    ): void {
        
        /* Guardar archivo en el sistema */
        $this->guardarArchivoFisico($file, $path, $fileName);

        /* Registrar imagen en base de datos */
        $fotocreada = Foto::create([
            'propiedad_id' => $propiedadId,
            'url'          => "/imagenes/{$folder}/{$fileName}",
            'notes'        => $descripcion,
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
    private function guardarVideo(
        $file,
        string $path,
        string $fileName,
        int $propiedadId,
        string $folder,
        ?string $descripcion
    ): void {
        /* Guardar archivo en el sistema */
        $this->guardarArchivoFisico($file, $path, $fileName);

        /* Registrar video en base de datos */
        Video::create([
            'propiedad_id' => $propiedadId,
            'url'          => "/videos/{$folder}/{$fileName}",
            'notes'        => $descripcion,
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
}
