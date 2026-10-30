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
     * GET /v1/repactado/{id}/conversacion
     */
    public function index(int $id): JsonResponse
    {
        try {
            if (!Historial_estado_repactado::whereKey($id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El repactado no existe.',
                ], 404);
            }

            $mensajes = Historial_conversacion_repactado::where('id_historial_estado_repactado', $id)
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
