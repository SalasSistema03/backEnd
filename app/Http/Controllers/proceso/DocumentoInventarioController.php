<?php

namespace App\Http\Controllers\proceso;

use App\Http\Controllers\Controller;
use App\Http\Controllers\proceso\Concerns\VerificaPermisoInventario;
use App\Models\proceso\Documento_inventario;
use App\Models\proceso\Proceso_propiedad;
use App\Services\Dpto_Tecnico\DocumentoInventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentoInventarioController extends Controller
{
    use VerificaPermisoInventario;

    private const MIME_WORD = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function __construct(private DocumentoInventarioService $service) {}

    // GET /documentosInventario?id_proceso_propiedad=
    public function index(Request $request): JsonResponse
    {
        $request->validate(['id_proceso_propiedad' => 'required|integer']);

        return response()->json([
            'success' => true,
            'data' => $this->service->listar((int) $request->id_proceso_propiedad),
        ]);
    }

    // POST /documentosInventario (multipart: id_proceso_propiedad, documento)
    public function store(Request $request): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $request->validate([
            'id_proceso_propiedad' => 'required|integer',
            'documento' => [
                'required',
                'file',
                'max:20480',
                'mimes:doc,docx',
                'mimetypes:' . implode(',', self::MIME_WORD),
            ],
        ]);

        $proceso = Proceso_propiedad::find($request->id_proceso_propiedad);
        if (!$proceso) {
            return response()->json(['success' => false, 'message' => 'Proceso no encontrado.'], 404);
        }

        try {
            $documento = $this->service->guardar($proceso, $request->file('documento'), auth('api')->id());

            return response()->json([
                'success' => true,
                'message' => 'Documento cargado correctamente.',
                'data' => $documento,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error al guardar documento de inventario: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'Error al guardar el documento.'], 500);
        }
    }

    // DELETE /documentosInventario/{id}
    public function destroy(int $id): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $documento = Documento_inventario::find($id);
        if (!$documento) {
            return response()->json(['success' => false, 'message' => 'Documento no encontrado.'], 404);
        }

        try {
            $this->service->eliminar($documento);

            return response()->json(['success' => true, 'message' => 'Documento eliminado.']);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar documento de inventario: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el documento.'], 500);
        }
    }
}
