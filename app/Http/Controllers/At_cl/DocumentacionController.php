<?php

namespace App\Http\Controllers\At_cl;


use App\Models\At_cl\Documentacion;
use App\Models\At_cl\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class DocumentacionController
{
    protected  $usuario, $usuario_id;

    public function __construct()
    {

        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        // Obtener el objeto completo del usuario
        $this->usuario = Usuario::find($this->usuario_id);
    }

    public function index() {}


    public function create() {}

    public function store(Request $request)
    {
        $propiedadId = session('propiedad_id');

        if ($request->hasFile('fotos')) {
            DB::beginTransaction();
            try {
                foreach ($request->file('fotos') as $index => $photo) {
                    $descripcion = $request->input("notes.{$index}.descripcion");
                    $idFolder = 'propiedad_' . $propiedadId;
                    $fileName = 'propiedad_' . $propiedadId  . '_' . time()  . '_' . $index . '.' . $photo->getClientOriginalExtension();
                   
                   $sharedFolderPathDocumentation = '\\\\10.10.10.152\\compartida\\DOCUMENTACION\\' . $idFolder;

                    if ($photo->getClientOriginalExtension() == 'pdf') {
                        if (!File::exists($sharedFolderPathDocumentation)) {
                            File::makeDirectory($sharedFolderPathDocumentation, 0777, true);
                        }
                        // Mover el archivo
                        $destinationPath = $sharedFolderPathDocumentation . '\\' . $fileName;
                        copy($photo->getPathname(), $destinationPath);
                        unlink($photo->getPathname());

                        Documentacion::create([
                            'propiedad_id' => $propiedadId,
                            'url' => '/documentos/' . $idFolder . '/' . $fileName,
                            'notes' => $descripcion,
                        ]);
                    } else {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'El archivo debe ser un PDF.');
                    }
                }
                DB::commit();
                return redirect()->back()->with('success', 'PDF subido correctamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                // Elimina archivos subidos si es necesario (opcional)
                if (isset($sharedFolderPathDocumentation) && isset($fileName) && File::exists($sharedFolderPathDocumentation . '/' . $fileName)) {
                    File::delete($sharedFolderPathDocumentation . '/' . $fileName);
                }
                return redirect()->back()->with('error', 'Hubo un error al subir el PDF. Intenta nuevamente.');
            }
        }
        return redirect()->back()->with('error', 'No se seleccionó ningún archivo.');
    }


    public function show(string $id)
    {
        $documentos = Documentacion::where('propiedad_id', $id)->get();
        session(['propiedad_id' => $id]);
        return view(
            'atencionAlCliente.propiedad.editarPropiedadDocumentos',
            compact('documentos')
        );
    }


    public function edit(string $id) {}


    public function update(Request $request, string $id)
    {
        $documento = Documentacion::findOrFail($id);
        $propiedadId = $documento->propiedad_id;
        $idFolder = 'propiedad_' . $propiedadId;

        DB::beginTransaction();
        try {
            // Si se está actualizando el PDF
            if ($request->hasFile('nueva_foto')) {
                $newPdf = $request->file('nueva_foto');

                // Verificar que sea un PDF
                if ($newPdf->getClientOriginalExtension() != 'pdf') {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'El archivo debe ser un PDF.');
                }

                // Definir rutas
                $sharedFolderPathDocumentation = '\\\\10.10.10.152\\compartida\\DOCUMENTACION\\' . $idFolder;
                $fileName = 'propiedad_' . $propiedadId . '_' . time() . '.' . $newPdf->getClientOriginalExtension();

                // Crear directorio si no existe
                if (!File::exists($sharedFolderPathDocumentation)) {
                    File::makeDirectory($sharedFolderPathDocumentation, 0777, true);
                }

                // Eliminar el PDF anterior si existe
                $oldPath = str_replace('/documentos/', '\\\\10.10.10.152\\compartida\\DOCUMENTACION\\', $documento->url);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }

                // Mover el nuevo PDF
                $destinationPath = $sharedFolderPathDocumentation . '\\' . $fileName;
                    copy($newPdf->getPathname(), $destinationPath);
                    unlink($newPdf->getPathname());


                // Actualizar la URL en la base de datos
                $documento->url = '/documentos/' . $idFolder . '/' . $fileName;
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


    public function destroy(string $id)
    {
        $documento = Documentacion::findOrFail($id);

        DB::beginTransaction();

        try {
            // Eliminar el archivo físico
            $filePath = str_replace('/documentos/', '\\\\10.10.10.152\\compartida\\DOCUMENTACION\\', $documento->url);

            if (File::exists($filePath)) {
                if (!File::delete($filePath)) {
                    throw new \Exception('No se pudo eliminar el archivo físico.');
                }
            }

            // Eliminar el registro de la base de datos
            $documento->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Documento eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error al eliminar el documento.');
        }
    }
}
