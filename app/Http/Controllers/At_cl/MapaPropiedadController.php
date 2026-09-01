<?php

namespace App\Http\Controllers\At_cl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\At_cl\MapaPropiedadService;


class MapaPropiedadController extends Controller
{
    protected $mapaService;

    // Inyectamos el servicio en el constructor
    public function __construct(MapaPropiedadService  $mapaService)
    {
        $this->mapaService = $mapaService;
    }

    /**
     * Retorna el listado de propiedades para ser dibujadas en Leaflet.
     */
    public function obtenerUbicaciones(Request $request): JsonResponse
    {
        // Recogemos todos los parámetros de la URL (?inmuebles[]=1&habitaciones=2)
        $filtros = $request->all();

        try {
            $propiedades = $this->mapaService->obtenerPropiedadesParaMapa($filtros);

            // Retornamos la respuesta en formato JSON para Vue
            return response()->json([
                'success' => true,
                'data' => $propiedades
            ], 200);

        } catch (\Exception $e) {
            // Manejo básico de errores
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones del mapa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}