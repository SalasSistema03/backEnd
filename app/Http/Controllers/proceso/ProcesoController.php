<?php

namespace App\Http\Controllers\proceso;

use App\Http\Controllers\Controller;
use App\Services\proceso\ProcesoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\contrato\ProcesoContratoService;
use App\Services\contable\sellado\RegistroSelladoService;


class ProcesoController extends Controller
{
    protected $reservaService;
    protected RegistroSelladoService $registroSelladoService;
    protected ProcesoContratoService $procesoContratoService;


    public function __construct(ProcesoService $reservaService, RegistroSelladoService $registroSelladoService, ProcesoContratoService $procesoContratoService)
    {
        $this->reservaService = $reservaService;
        $this->registroSelladoService = $registroSelladoService;
        $this->procesoContratoService = $procesoContratoService;
    }

    public function subirReservas(Request $request)
    {
        DB::beginTransaction();
        try {
            $usuarioId = auth('api')->id();
            $data = $request->all();

            // Mover archivo a data para el service
            if ($request->hasFile('comprobante')) {
                $data['comprobante'] = $request->file('comprobante');
            }

            $result = $this->reservaService->subirReserva($data, $usuarioId);

            DB::commit();
            return response()->json([
                'success' => true,
                'path' => $result['comprobantePath']
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error subirReservas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function obtenerReservas(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();
            $estado = $request->input('estado');
            $mes = $request->input('mes');
            $reservas = $this->reservaService->obtenerReservas($usuarioId, $estado, $mes);

            return response()->json(['resultado' => $reservas]);
        } catch (\Exception $e) {
            Log::error('Error obtenerReservas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener reservas.'], 500);
        }
    }

    public function guardarEstado(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();
            $data = $request->all();

            $historial = $this->reservaService->guardarEstado($data, $usuarioId);

            return response()->json(['success' => true, 'data' => $historial]);
        } catch (\Exception $e) {
            Log::error('Error guardarEstado: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar el estado.'], 500);
        }
    }

    public function getHistorial(Request $request)
    {
        try {
            $idProceso = $request->input('id');
            $historial = $this->reservaService->getHistorial($idProceso);

            return response()->json(['resultado' => $historial]);
        } catch (\Exception $e) {
            Log::error('Error getHistorial: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener historial.'], 500);
        }
    }

    public function getReservaIdentificadas(Request $request)
    {
        try {
            $montoAceptado = $request->input('data');
            $reservas = $this->reservaService->getReservasIdentificadas($montoAceptado);

            return response()->json(['resultado' => $reservas]);
        } catch (\Exception $e) {
            Log::error('Error getReservaIdentificadas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener reservas identificadas.'], 500);
        }
    }

    public function guardarReservaIdentificada(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();
            $idProceso = $request->input('id');

            $result = $this->reservaService->guardarReservaIdentificada($idProceso, $usuarioId);

            if (!$result) {
                return response()->json(['error' => 'Proceso no encontrado'], 404);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error guardarReservaIdentificada: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar la reserva.'], 500);
        }
    }

    public function obtenerComprobante(Request $request)
    {
        try {
            $ruta = $request->input('documentacion');
            $comprobante = $this->reservaService->obtenerComprobante($ruta);

            return response($comprobante['content'])
                ->header('Content-Type', $comprobante['contentType'])
                ->header('Content-Disposition', 'inline; filename="' . $comprobante['filename'] . '"');
        } catch (\Exception $e) {
            Log::error('Error obtenerComprobante: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEstadosContrato()
    {
        try {

            $estados = (new ProcesoContratoService())->EstadosContrato();

            return response()->json(['resultado' => $estados]);
        } catch (\Exception $e) {
            Log::error('Error getEstadosContrato: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener estados del contrato.'], 500);
        }
    }

    public function getHistorialContrato(Request $request)
    {
        try {
            $form = $request->input('form');
            $historial = (new ProcesoContratoService())->getHistorialContrato($form);

            return response()->json(['resultado' => $historial]);
        } catch (\Exception $e) {
            Log::error('Error getHistorialContrato: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener el historial.'], 500);
        }
    }


    public function ActualizarEstadoContrato(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();

            $proceso = $this->procesoContratoService->actualizarEstadoContrato($request->all(), $usuarioId);

            return response()->json(['success' => true, 'data' => $proceso]);
        } catch (\Exception $e) {
            Log::error('Error ActualizarEstadoContrato: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getObservacionesContratoNuevo(Request $request)
    {
        try {
            $observaciones = (new ProcesoContratoService())->getObservacionesContratoNuevo($request->all());

            return response()->json(['resultado' => $observaciones]);
        } catch (\Exception $e) {
            Log::error('Error getObservacionesContratoNuevo: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener observaciones del contrato.'], 500);
        }
    }
}
