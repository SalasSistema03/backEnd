<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Foto;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class FotoController
{
    protected  $usuario, $usuario_id;

    public function __construct()
    {

        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        // Obtener el objeto completo del usuario
        $this->usuario = Usuario::find($this->usuario_id);
    }



    public function index() {}


    public function create()
    {
        $propiedades = Propiedad::all(); // Para seleccionar una propiedad existente
        return view('fotos.create', compact('propiedades'));
    }


    public function store(Request $request)
    {
        
        $propiedadId = session('propiedad_id');

        if ($request->hasFile('fotos')) {
            DB::beginTransaction();

            try {
                foreach ($request->file('fotos') as $index => $photo) {
                    // Obtener la descripción asociada a la foto
                    $descripcion = $request->input("notes.{$index}.descripcion");

                    // Definir carpeta y nombre del archivo
                    $idFolder = 'propiedad_' . $propiedadId;
                    $fileName = 'propiedad_' . $propiedadId  . '_' . $index . '.' . $photo->getClientOriginalExtension();

                    // Ruta de la carpeta compartida en el servidor
                    $sharedFolderPathImage = '\\\\10.10.10.151\\compartida\\PROPIEDADES\\' . $idFolder;
               

                    if (
                        $photo->getClientOriginalExtension() == 'jpeg' ||
                        $photo->getClientOriginalExtension() == 'jpg'
                    ) {
                        // Crear la carpeta si no existe
                        if (!File::exists($sharedFolderPathImage)) {
                            File::makeDirectory($sharedFolderPathImage, 0777, true);
                        }
                        $fullPath = $sharedFolderPathImage . '/' . $fileName;

                        // Mover la imagen a la carpeta compartida
                        $destinationPath = $sharedFolderPathImage . '\\' . $fileName;
                        copy($photo->getPathname(), $destinationPath);
                        unlink($photo->getPathname());
                        // Guardar en la base de datos
                        $url_foto = Foto::create([
                            'propiedad_id' => $propiedadId,
                            'url' => '/imagenes/' . $idFolder . '/' . $fileName,
                            'notes' => $descripcion,
                        ]);
                    } else {
                        return redirect()->back()->with('error', 'Formato de archivo no válido. Solo se permiten archivos JPEG o JPG.');
                    }
                }

                DB::commit();
                return redirect()->back()->with('success', 'Foto subida correctamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                // Intentar eliminar el archivo físico si fue subido
                if (isset($fullPath) && File::exists($fullPath)) {
                    File::delete($fullPath);
                }

                return redirect()->back()->with('error', 'Error al subir la foto:');
            }
        }

        return redirect()->back()->with('error', 'No se seleccionó ningún archivo.');
    }



    public function show($id)
    {
        $fotos = Foto::where('propiedad_id', $id)
        ->orderByRaw('orden IS NULL')
        ->orderBy('orden', 'asc')
        ->get() ;
       
        // Obtiene las URLs de las fotos y reemplaza las barras invertidas por barras normales
        session(['propiedad_id' => $id]);

        return view('atencionAlCliente.propiedad.editarPropiedadFotos', compact('fotos'));
    }


    public function edit(string $id) {}


    public function update(Request $request, Foto $foto)
    {
        /* dd($request); */
        $propiedadId = session('propiedad_id');

        DB::beginTransaction();

        try {
           /*  dd($request->input('orden')); */
            // Si se sube una nueva imagen, la reemplaza
            if ($request->hasFile('nueva_foto')) {
                $photo = $request->file('nueva_foto');

                // Definir carpeta y nombre del archivo 'D:/propiedades/'
                $idFolder = 'propiedad_' . $propiedadId;
                $fileName = 'propiedad_' . $propiedadId . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $sharedFolderPathImage = '\\\\10.10.10.151\\compartida\\PROPIEDADES\\' . $idFolder;
                // Verificar que sea una imagen válida
                if (in_array($photo->getClientOriginalExtension(), ['jpeg', 'jpg'])) {
                    // Crear la carpeta si no existe
                    if (!File::exists($sharedFolderPathImage)) {
                        File::makeDirectory($sharedFolderPathImage, 0777, true);
                    }
                    // Eliminar la imagen anterior
                    $oldFilePath = public_path(str_replace('/imagenes/', $sharedFolderPathImage, $foto->url));
                    if (File::exists($oldFilePath)) {
                        File::delete($oldFilePath);
                    }

                    // Mover la nueva imagen
                    $destinationPath = $sharedFolderPathImage . '\\' . $fileName;
                        copy($photo->getPathname(), $destinationPath);
                        unlink($photo->getPathname());

                    // Actualizar en la base de datos
                    $foto->update([
                        'url' => '/imagenes/' . $idFolder . '/' . $fileName,
                        
                    ]);
                   

                } else {
                    return redirect()->back()->with('error', 'Formato de archivo no válido. Solo se permiten JPEG o JPG.');
                }
            }
           /*  dd($request->input('orden')); */
             $foto->update(['orden' => $request->input('orden')]); 
            // Si se actualiza la nota
            if ($request->filled('notes')) {
                $foto->update(['notes' => $request->input('notes')]);
            }
         
            DB::commit();
            return redirect()->back()->with('success', 'Foto actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Intentar eliminar la nueva imagen si ya fue copiada
            if (isset($sharedFolderPathImage, $fileName)) {
                $newFilePath = $sharedFolderPathImage . '\\' . $fileName;
                if (File::exists($newFilePath)) {
                    File::delete($newFilePath);
                }
            }

            return redirect()->back()->with('error', 'Error al actualizar la foto.');
        }
    }


    public function destroy(Foto $foto)
    {
        // Obtener la ruta del archivo en la carpeta compartida
        $filePath = str_replace('/imagenes/', '\\\\10.10.10.151\\compartida\\PROPIEDADES\\', $foto->url);
        // Eliminar la imagen del servidor si existe
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Eliminar el registro de la base de datos
        $foto->delete();

        return redirect()->back()->with('success', 'Foto eliminada correctamente.');
    }
}
