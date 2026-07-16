<?php

namespace App\Http\Controllers\proceso;

use App\Http\Controllers\Controller;
use App\Services\proceso\ProcesoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\proceso\Estado_reserva;
use App\Models\proceso\Proceso_propiedad;
use App\Services\contrato\ProcesoContratoService;
use App\Models\proceso\Estado_contrato;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\usuarios_y_permisos\Usuario;
use App\Notifications\RecordatorioNotificacion;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Propiedad;




class ProcesoController extends Controller
{
    protected $reservaService;

    public function __construct(ProcesoService $reservaService)
    {
        $this->reservaService = $reservaService;
    }

    public function subirReservas(Request $request)
    {
        Log::info('llego');
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

        Log::info('ActualizarEstadoContrato request: ', $request->all());
        $data = (new ProcesoContratoService())->crearHistorialEstadoContrato($request->all());

        $proceso = Proceso_propiedad::find($request->id_proceso);
        $asesor = $proceso->asesor;
        $proceso->update(['id_historial_estado_contrato' => $data->id]);

        //parte de notificacion
        $usuarioId =   auth('api')->id();
        $usuario = Usuario::find($usuarioId);

        //buscamos el cod_ de la propiedad
        $propiedad = Propiedad::where('id', $proceso->id_propiedad)->first();
        $empresaPropiedad = Empresas_propiedades::where('propiedad_id', $propiedad->id)->first();
        $folio = $empresaPropiedad->folio ?? 'N/D';
        $mensaje = [
            'descripcion'       => "El folio " . $folio . " a cambiado de estado",
            'fecha'             => now()->isoFormat('DD/MM/YYYY'),  // 13/04/2026
            'hora'              => now()->isoFormat('HH:mm'),       // 14:53
            'activo'            => 1,
            'usuarioNotificar'  => $asesor,
            'cliente_id'        => null,
            'id_criterio_venta' => null,
            'pertenece'         => "reserva",
            'folio'             => $folio
        ];

        if ($request->id_estado == 8) {
            historial_estado_reserva::where('id', $proceso->id_historial_estado_reserva)->update(['id_estado' => 2]);
            if ($usuario) {
                $usuario->notify(new RecordatorioNotificacion($mensaje));
            }
        } elseif ($request->id_estado == 9) {
            historial_estado_reserva::where('id', $proceso->id_historial_estado_reserva)->update(['id_estado' => 1]);
            if ($usuario) {
                $usuario->notify(new RecordatorioNotificacion($mensaje));
            }
        }
    }
}
