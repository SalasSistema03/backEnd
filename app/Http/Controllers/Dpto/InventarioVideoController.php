<?php

namespace App\Http\Controllers\Dpto;

use App\Http\Controllers\Controller;
use App\Http\Controllers\proceso\Concerns\VerificaPermisoInventario;
use App\Models\Dpto\InventarioVideo;
use App\Models\proceso\Proceso_propiedad;
use App\Services\Dpto_Tecnico\InventarioVideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventarioVideoController extends Controller
{
    use VerificaPermisoInventario;

    public function __construct(private InventarioVideoService $service)
    {
    }

    public function store(Request $request): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $request->validate([
            'id_proceso_propiedad' => 'required|integer',
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400',
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $proceso = Proceso_propiedad::find($request->id_proceso_propiedad);
        if (!$proceso) {
            return response()->json(['success' => false, 'message' => 'Proceso no encontrado.'], 404);
        }

        try {
            $video = $this->service->guardar(
                $proceso,
                $request->file('video'),
                $request->input('descripcion'),
            );

            return response()->json(['success' => true, 'data' => $video], 201);
        } catch (\Throwable $e) {
            Log::error('Error al guardar video de inventario: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json(['success' => false, 'message' => 'Error al guardar el video.'], 500);
        }
    }

    public function index(int $idProcesoPropiedad): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->listar($idProcesoPropiedad)]);
    }

    public function destroy(int $id): JsonResponse
    {
        if ($denegado = $this->sinPermiso()) {
            return $denegado;
        }

        $video = InventarioVideo::find($id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video no encontrado.'], 404);
        }

        try {
            $this->service->eliminar($video);
            return response()->json(['success' => true, 'message' => 'Video eliminado.']);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar video de inventario: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el video.'], 500);
        }
    }
}
