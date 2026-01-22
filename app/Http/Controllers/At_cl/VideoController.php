<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Video;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class VideoController
{
    protected  $usuario, $usuario_id;

    public function __construct()
    {
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        // Obtener el objeto completo del usuario
        $this->usuario = Usuario::find($this->usuario_id);
    }



    public function index() {}


    public function create(){}


    public function store(Request $request)
    {
        $propiedadId = session('propiedad_id');

        if ($request->hasFile('videos')) {
            DB::beginTransaction();

            try {
                foreach ($request->file('videos') as $index => $video) {
                    // Obtener la descripción asociada a la video
                    $descripcion = $request->input("notes.{$index}.descripcion");

                    // Definir carpeta y nombre del archivo
                    $idFolder = 'propiedad_' . $propiedadId;
                    $fileName = 'propiedad_' . $propiedadId  . '_' . $index . '.' . $video->getClientOriginalExtension();

                    // Ruta de la carpeta compartida en el servidor
                    $sharedFolderPathVideos = '\\\\10.10.10.153\\compartida\\VIDEOS\\' . $idFolder;
               

                    if (
                         $video->getClientOriginalExtension() == 'mp4' ||
                        $video->getClientOriginalExtension() == 'MOV' ||
                        $video->getClientOriginalExtension() == 'mov'
                    ) {
                        // Crear la carpeta si no existe
                        if (!File::exists($sharedFolderPathVideos)) {
                            File::makeDirectory($sharedFolderPathVideos, 0777, true);
                        }

                        $fullPath = $sharedFolderPathVideos . '/' . $fileName;

                        // Mover la imagen a la carpeta compartida
                        $destinationPath = $sharedFolderPathVideos . '\\' . $fileName;
                        copy($video->getPathname(), $destinationPath);
                        unlink($video->getPathname());
                        // Guardar en la base de datos
                        $url_video = Video::create([
                            'propiedad_id' => $propiedadId,
                            'url' => '/videos/' . $idFolder . '/' . $fileName,
                            'notes' => $descripcion,
                        ]);
                    } else {
                        return redirect()->back()->with('error', 'Formato de archivo no válido. Solo se permiten archivos MP4.');
                    }
                }

                DB::commit();
                return redirect()->back()->with('success', 'Video subido correctamente.');
            } catch (\Exception $e) {
                DB::rollBack();

                // Intentar eliminar el archivo físico si fue subido
                if (isset($fullPath) && File::exists($fullPath)) {
                    File::delete($fullPath);
                }

                return redirect()->back()->with('error', 'Error al subir la video:');
            }
        }

        return redirect()->back()->with('error', 'No se seleccionó ningún archivo.');
    }

    
    public function show($id)
    {
        
         $videos = Video::where('propiedad_id', $id)->get();
        // Obtiene las URLs de las videos y reemplaza las barras invertidas por barras normales
        session(['propiedad_id' => $id]);

        return view('atencionAlCliente.propiedad.editarPropiedadVideos', compact('videos')); 
    }

    public function edit(string $id) {}


    
    public function update(Request $request, string $id)
    {
        $documento = Video::findOrFail($id);
        $propiedadId = $documento->propiedad_id;
        $idFolder = 'propiedad_' . $propiedadId;

        DB::beginTransaction();
        try {
            // Si se está actualizando el PDF
            if ($request->hasFile('nueva_video')) {
                $newPdf = $request->file('nueva_video');

                // Verificar que sea un PDF
                if ($newPdf->getClientOriginalExtension() != 'mp4') {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'El archivo debe ser un PDF.');
                }

                // Definir rutas
                $sharedFolderPathDocumentation = '\\\\10.10.10.153\\compartida\\VIDEOS\\' . $idFolder;
                $fileName = 'propiedad_' . $propiedadId . '_' . time() . '.' . $newPdf->getClientOriginalExtension();

                // Crear directorio si no existe
                if (!File::exists($sharedFolderPathDocumentation)) {
                    File::makeDirectory($sharedFolderPathDocumentation, 0777, true);
                }

                // Eliminar el PDF anterior si existe
                $oldPath = str_replace('/videos/', '\\\\10.10.10.153\\compartida\\VIDEOS\\', $documento->url);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }

                // Mover el nuevo PDF
                $destinationPath = $sharedFolderPathDocumentation . '\\' . $fileName;
                    copy($newPdf->getPathname(), $destinationPath);
                    unlink($newPdf->getPathname());


                // Actualizar la URL en la base de datos
                $documento->url = '/videos/' . $idFolder . '/' . $fileName;
            }

            // Si se está actualizando la nota
            if ($request->has('notes')) {
                $documento->notes = $request->notes;
            }

            $documento->save();
            DB::commit();
            return redirect()->back()->with('success', 'Documento actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Opcional: restaurar el PDF anterior si lo habías borrado y el nuevo falló
            return redirect()->back()->with('error', 'Error al actualizar el PDF: ');
        }
    }

    public function destroy(Video $video)
    {
        // Obtener la ruta del archivo en la carpeta compartida
        /* $filePath = public_path(str_replace('/imagenes/', 'D:/propiedades/', $video->url)); */
        $filePath = str_replace('/videos/', '\\\\10.10.10.151\\compartida\\VIDEOS\\', $video->url);
        // Eliminar la imagen del servidor si existe
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Eliminar el registro de la base de datos
        $video->delete();

        return redirect()->back()->with('success', 'Video eliminado correctamente.');
    }
}
