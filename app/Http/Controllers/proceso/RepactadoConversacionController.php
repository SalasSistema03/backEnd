<?php

namespace App\Http\Controllers\proceso;

use App\Http\Controllers\Controller;
use App\Models\proceso\Historial_conversacion_repactado;
use App\Models\proceso\Historial_estado_repactado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Conversación (estilo WhatsApp) de un repactado.
 *
 * Quien carga el mensaje elige si lo dijo el propietario o el inquilino. La
 * fecha y hora se toma del servidor al momento de guardar.
 */
class RepactadoConversacionController extends Controller
{
    /**
     * Lista los mensajes de un repactado en orden cronológico.
     *
     * La conversación es de la propiedad: se traen los mensajes de todos los
     * historiales de repactado que compartan el mismo propiedad_id.
     *
     * GET /v1/repactado/{id}/conversacion
     */
    public function index(int $id): JsonResponse
    {
        try {
            $historial = Historial_estado_repactado::find($id);
            if (!$historial) {
                return response()->json([
                    'success' => false,
                    'message' => 'El repactado no existe.',
                ], 404);
            }

            $idsHistoriales = $this->idsHistorialesDeLaPropiedad($historial);

            $mensajes = Historial_conversacion_repactado::whereIn('id_historial_estado_repactado', $idsHistoriales)
                ->orderBy('fecha_hora')
                ->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $mensajes,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error al obtener conversación de repactado: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener la conversación.',
            ], 500);
        }
    }

    /**
     * Ids de todos los historiales de repactado de la misma propiedad, incluido el actual.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function idsHistorialesDeLaPropiedad(Historial_estado_repactado $historial)
    {
        if (empty($historial->propiedad_id)) {
            return collect([$historial->id]);
        }

        return Historial_estado_repactado::where('propiedad_id', $historial->propiedad_id)
            ->pluck('id')
            ->push($historial->id)
            ->unique()
            ->values();
    }

    /**
     * Agrega un mensaje a la conversación de un repactado.
     *
     * POST /v1/repactado/{id}/conversacion
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate([
            'tipo_persona' => ['required', 'in:propietario,inquilino'],
            'mensaje' => ['required', 'string', 'max:5000'],
        ]);

        try {
            if (!Historial_estado_repactado::whereKey($id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El repactado no existe.',
                ], 404);
            }

            $mensaje = Historial_conversacion_repactado::create([
                'id_historial_estado_repactado' => $id,
                'tipo_persona' => $datos['tipo_persona'],
                'fecha_hora' => now(),
                'mensaje' => $datos['mensaje'],
                'id_usuario' => auth('api')->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $mensaje,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error al guardar mensaje de repactado: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el mensaje.',
            ], 500);
        }
    }
}
