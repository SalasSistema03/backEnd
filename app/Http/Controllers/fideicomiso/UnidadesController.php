<?php

namespace App\Http\Controllers\fideicomiso;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // <-- 1. Agregamos el Request genérico de Laravel
use App\Services\fideicomiso\UnidadesService;
use Illuminate\Http\JsonResponse;
use Exception;

class UnidadesController extends Controller
{
    protected UnidadesService $unidadesService;

    public function __construct(UnidadesService $unidadesService)
    {
        $this->unidadesService = $unidadesService;
    }

    public function getUnidadesController(): JsonResponse
    {
        try {
            $unidades = $this->unidadesService->obtenerTodas();
            return response()->json([
                'status' => 'success',
                'data'   => $unidades
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Error al obtener las unidades',
                'ErrorBase' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnidadPorIdController(int $id): JsonResponse
    {
        try {
            $unidad = $this->unidadesService->obtenerPorId($id);
            if (!$unidad) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unidad no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $unidad
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Error al buscar la unidad',
                'ErrorBase' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Le indicamos a Laravel que es un Request
    public function postUnidadController(Request $request): JsonResponse
    {
        try {
            // 3. Pasamos los datos como array usando ->all()
            $unidad = $this->unidadesService->guardar($request->all());
            return response()->json([
                'status'  => 'success',
                'message' => 'Unidad guardada correctamente',
                'data'    => $unidad
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Error al guardar la unidad',
                'ErrorBase' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Le indicamos a Laravel que es un Request
    public function modificarUnidadController(Request $request, int $id): JsonResponse
    {
        try {
            // 3. Pasamos los datos como array usando ->all()
            $unidad = $this->unidadesService->actualizar($id, $request->all());
            if (!$unidad) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unidad no encontrada para actualizar'
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Unidad actualizada correctamente',
                'data'    => $unidad
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Error al actualizar la unidad',
                'ErrorBase' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarUnidadController(int $id): JsonResponse
    {
        try {
            $eliminado = $this->unidadesService->eliminar($id);
            if (!$eliminado) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unidad no encontrada'
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Unidad eliminada correctamente'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Error al eliminar la unidad',
                'ErrorBase' => $e->getMessage()
            ], 500);
        }
    }
}