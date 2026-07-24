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
use App\Services\contable\sellado\RegistroSelladoService;
use App\Models\proceso\Estado_contrato;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\usuarios_y_permisos\Usuario;
use App\Notifications\RecordatorioNotificacion;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Propiedad;
use App\Models\Contable\Sellado\Registro_sellado;
use App\Models\sys\Contratos_cabecera_sys;
use App\Models\sys\Padron_sys;
use App\Models\sys\Propiedades_sys;

class ProcesoController extends Controller
{
    protected $reservaService;
    protected RegistroSelladoService $registroSelladoService;
    protected $prueba;


    public function __construct(ProcesoService $reservaService, RegistroSelladoService $registroSelladoService)
    {
        $this->reservaService = $reservaService;
        $this->registroSelladoService = $registroSelladoService;
    }

    public function subirReservas(Request $request)
    {
        //Log::info('llego');
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

        //Log::info('ActualizarEstadoContrato request: ', $request->all());
        //dd("actualizarEstadoContrato");


        $data = (new ProcesoContratoService())->crearHistorialEstadoContrato($request->all());

        $proceso = Proceso_propiedad::find($request->id_proceso);
        if ($proceso && $request->cant_meses) {

            $proceso->update(['meses_contrato' => $request->cant_meses]);
        }
        $asesor = $proceso->asesor;
        $proceso->update(['id_historial_estado_contrato' => $data->id]);

        //parte de notificacion
        $usuarioId =   auth('api')->id();
        $usuario = Usuario::find($usuarioId);

        //buscamos el cod_ de la propiedad
        $propiedad = Propiedad::where('id', $proceso->id_propiedad)->first();
        $empresaPropiedad = Empresas_propiedades::where('propiedad_id', $propiedad->id)->first();
        $folio = $empresaPropiedad->folio ?? 'N/D';



        if (
            $request->monto != null || $request->monto_contrato != null || $request->chojas != null ||
            $request->informe != null || $request->CantInforme != null || $request->contrato != null || $request->inquilino_propietario != null
        ) {


            if ($request->folio) {
                $buscarFolioSellado = Registro_sellado::where('folio', $request->folio[0]['folio'])->exists();

                $idCasa = Propiedades_sys::where('carpeta', $request->folio[0]['folio'])->value('id_casa');
                $idEmpresa = $request->folio[0]['empresa_id'];


                $contrato = Contratos_cabecera_sys::where('id_casa', $idCasa)
                    ->where('id_empresa', $idEmpresa)
                    ->orderByDesc('id_contrato_cabecera')
                    ->first(['id_inquilino', 'comienza']);

                $id_inquilino = $contrato?->id_inquilino;
                $comienza = $contrato?->comienza;

                if ($buscarFolioSellado) {

                    $folioEncontrado = Registro_sellado::where('folio', $request->folio[0]['folio'])->first();
                    if ($folioEncontrado->mostrar != 0) {
                        return response()->json(['error' => 'Folio ya calculado']);
                    } else {

                        //Log::info($folioEncontrado);
                        $folioEncontrado->update([
                            'mostrar'                  => 0,
                            'folio'                    => $request->folio[0]['folio'],
                            'empresa'                  => $idEmpresa,
                            'nombre'                   => $nombre_inquilino ?? '', //nombre del inquilino
                            'cantidad_meses'           => $request->cant_meses, //c/meses
                            'monto_documento'          => $request->monto,
                            'monto_contrato'           => $request->monto_contrato ?? null,
                            'hojas'                    => $request->chojas ?? null,
                            'informe'                  => $request->informe ?? null,
                            'cantidad_informes'        => $request->CantInforme ?? null,
                            'tipo_contrato'            => $request->tipo_contrato ?? null,
                            'inq_prop'                 => $request->inquilino_propietario ?? null,
                            'fecha_inicio'             => $comienza,
                            'usuario_id'               => $usuario->id,

                        ]);
                    }
                } else {

                    //Log::info('entro mal');




                    if ($id_inquilino != null) {
                        $nombre_inquilino = Padron_sys::where('id_inquilino', $id_inquilino)->value('razon_social');
                    }


                    $registro = Registro_sellado::create([
                        'mostrar'                  => 0,
                        'folio'                    => $request->folio[0]['folio'],
                        'empresa'                  => $idEmpresa,
                        'nombre'                   => $nombre_inquilino ?? '', //nombre del inquilino
                        'cantidad_meses'           => $request->cant_meses, //c/meses
                        'monto_documento'          => $request->monto,
                        'monto_contrato'           => $request->monto_contrato ?? null,
                        'hojas'                    => $request->chojas ?? null,
                        'informe'                  => $request->informe ?? null,
                        'cantidad_informes'        => $request->CantInforme ?? null,
                        'tipo_contrato'            => $request->tipo_contrato ?? null,
                        'inq_prop'                 => $request->inquilino_propietario ?? null,
                        'fecha_inicio'             => $comienza,
                        'usuario_id'               => $usuario->id,
                    ]);

                    //Log::info("registro: " . $registro);

                    $proceso->update([
                        'id_registro_sellado' => $registro->id_registro_sellado,
                    ]);
                }
            }
        }

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

    public function getObservacionesContratoNuevo(Request $request)
    {

        Log::info('llego al controlador');
        try {
            $observaciones = (new ProcesoContratoService())->getObservacionesContratoNuevo($request->all());

            return response()->json(['resultado' => $observaciones]);
        } catch (\Exception $e) {
            Log::error('Error getObservacionesContratoNuevo: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener observaciones del contrato.'], 500);
        }
    }
}
