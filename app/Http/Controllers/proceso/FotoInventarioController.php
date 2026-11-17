<?php

namespace App\Http\Controllers\proceso;

use App\Http\Controllers\Controller;
use App\Http\Controllers\proceso\Concerns\VerificaPermisoInventario;
use App\Models\proceso\Foto_inventario;
use App\Models\proceso\Proceso_propiedad;
use App\Services\Dpto_Tecnico\FotoInventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FotoInventarioController extends Controller
{
    use VerificaPermisoInventario;

    public function __construct(private FotoInventarioService $service)
    {
    }

    // GET /fotosInventario?id_proceso_propiedad=
    public function index(Request $request): JsonResponse
    {
        $request->validate(['id_proceso_propiedad' => 'required|integer']);

        return response()->json([
            'success' => true,
            'data' => $this->service->listar((int) $request->id_proceso_propiedad),
        ]);
    }

    // POST /fotosInventario (multipart: id_proceso_propiedad, fotos[], comentarios[])
    public function store(Request $request): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $request->validate([
            'id_proceso_propiedad' => 'required|integer',
            'fotos' => 'required|array|min:1',
            'fotos.*' => 'file|mimes:jpg,jpeg,png|max:20480',
            'comentarios' => 'nullable|array',
            'comentarios.*' => 'nullable|string|max:2000',
        ]);

        $proceso = Proceso_propiedad::find($request->id_proceso_propiedad);
        if (!$proceso) {
            return response()->json(['success' => false, 'message' => 'Proceso no encontrado.'], 404);
        }

        try {
            $creadas = $this->service->guardar(
                $proceso,
                $request->file('fotos'),
                $request->input('comentarios', []),
                auth('api')->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Fotos cargadas correctamente.',
                'data' => $creadas,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error al guardar fotos de inventario: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'Error al guardar las fotos.'], 500);
        }
    }

    // PATCH /fotosInventario/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $request->validate(['notes' => 'nullable|string|max:2000']);

        $foto = Foto_inventario::find($id);
        if (!$foto) {
            return response()->json(['success' => false, 'message' => 'Foto no encontrada.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->service->actualizarComentario($foto, $request->notes),
        ]);
    }

    // DELETE /fotosInventario/{id}
    public function destroy(int $id): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $foto = Foto_inventario::find($id);
        if (!$foto) {
            return response()->json(['success' => false, 'message' => 'Foto no encontrada.'], 404);
        }

        try {
            $this->service->eliminar($foto);

            return response()->json(['success' => true, 'message' => 'Foto eliminada.']);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar foto de inventario: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'No se pudo eliminar la foto.'], 500);
        }
    }
}
