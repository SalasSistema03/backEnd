<?php

namespace App\Services\contrato;

use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\Contable\Sellado\Registro_sellado;
use App\Models\proceso\Estado_contrato;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Historial_estado_dpto;
use App\Models\proceso\Proceso_propiedad;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\sys\Contratos_cabecera_sys;
use App\Models\sys\Padron_sys;
use App\Models\sys\Propiedades_sys;
use App\Models\usuarios_y_permisos\Usuario;
use App\Notifications\RecordatorioNotificacion;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcesoContratoService
{
    public function EstadosContrato()
    {
        return Estado_contrato::all();
    }

    public function getHistorialContrato($form)
    {
        $query = Proceso_propiedad::with([
            'propiedad.folios',
            'cliente',
            'asesorUsuario',
            'historialEstadoReserva',
            'historialEstadoContrato.estado',
            'historialEstadoContrato.tirillaEntregadaPor',
            'historialEstadoContrato.tirillaControladaPor',
            'historialEstadoDpto.quien_cargo',
            'propiedad.calle',
            'registroSellado',
        ])->whereNotNull('id_historial_estado_contrato');

        // Filter by year and month
        if (!empty($form['mes']) && !empty($form['anio'])) {
            $query->whereYear('fecha_reserva', $form['anio'])
                ->whereMonth('fecha_reserva', $form['mes']);
        }

        // Filter by state (which is in historialEstadoContrato -> id_estado)
        if (!empty($form['filtroEstado'])) {
            $query->whereHas('historialEstadoContrato', function ($q) use ($form) {
                $q->where('id_estado', $form['filtroEstado']);
            });
        }

        // Filter by advisor
        if (!empty($form['filtroAsesor'])) {
            $query->where('asesor', $form['filtroAsesor']);
        }


        if (!empty($form['folio'])) {
            $propiedadIds = Propiedad::whereHas('folios', function ($q) use ($form) {
                $q->where('folio', 'like', '%' . $form['folio'] . '%');
            })->pluck('id');

            $query->whereIn('id_propiedad', $propiedadIds);
        }

        $res = $query->get();

        foreach ($res as $proceso) {
            $historialEstadoContrato = $proceso->historialEstadoContrato;
            $folio = $proceso->propiedad?->folios?->first()?->folio;

            if ($historialEstadoContrato?->gastos_administrativos === null && $folio !== null) {
                $folioEncontrado = Registro_sellado::where('folio', $folio)->first();
                if ($folioEncontrado) {
                    $historialEstadoContrato->gastos_administrativos = $folioEncontrado->gasto_administrativo;
                }
            }
        }
        return $res;
    }

    public function crearHistorialEstadoContrato(array $request)
    {
        $usuarioId =   auth('api')->id();
        $usuario = Usuario::find($usuarioId);

        $data = historial_estado_contrato::create([
            'id_estado' => $request['id_estado'] ?? null,
            'observaciones' => $request['observaciones'] ?? 'Modificacion General',
            'fecha_comercial_presenta_carpeta' => $request['fecha_comercial_presenta_carpeta'] ?? null,
            'fecha_preaprobada' => $request['fecha_preaprobada'] ?? null,
            'fecha_reserva' => $request['fecha_reserva'] ?? null,
            'gastos_administrativos' => $request['gastos_administrativos'] ?? null,
            'cuotas_ga' => $request['cuotas'] ?? null,
            'fecha_contrato' => $request['fecha_contrato'] ?? null,
            'fecha_autorizacion' => $request['fecha_autorizacion'] ?? null,
            'fecha_finalizacion_firma_cobro' => $request['fecha_finalizacion_firma_cobro'] ?? null,
            'quien_cargo' => $usuario->id ?? null,
            'fecha_carga' => now()->format('Y-m-d H:i:s'),
            'id_proceso_propiedad' => $request['id_proceso'] ?? null,
        ]);

        return $data;
    }

    public function getObservacionesContratoNuevo($idProceso)
    {
        $observaciones = Historial_estado_contrato::where('id_proceso_propiedad', $idProceso)->get(['observaciones', 'fecha_carga', 'quien_cargo', 'id_estado']);
        foreach ($observaciones as $observacion) {
            $usuario = Usuario::find($observacion->quien_cargo);
            $observacion->nombre_usuario = $usuario ? $usuario->username : 'Usuario no encontrado';
            $estado = Estado_contrato::find($observacion->id_estado);
            $observacion->nombre_estado = $estado ? $estado->estado : 'Estado no encontrado';
        }
        return $observaciones;
    }

    /**
     * Orquesta la actualización completa del estado del contrato:
     * crea el historial, actualiza el proceso, gestiona el sellado
     * y dispara la notificación/estado de reserva correspondiente.
     *
     * @throws \RuntimeException si el proceso no existe o el folio ya fue calculado.
     */
    public function actualizarEstadoContrato(array $request, $usuarioId)
    {
        /* Log::info([$request]);
        dd('hola'); */
        return DB::transaction(function () use ($request, $usuarioId) {

            $historialEstadoContrato = $this->crearHistorialEstadoContrato($request);

            $proceso = Proceso_propiedad::find($request['id_proceso'] ?? null);
            if (!$proceso) {
                throw new \RuntimeException('Proceso no encontrado.');
            }

            $this->actualizarDatosProceso($proceso, $request, $historialEstadoContrato->id);

            $usuario = Usuario::find($usuarioId);
            $folio = $this->obtenerFolioPropiedad($proceso->id_propiedad);

             /* if ($this->requiereProcesarSellado($request)) {
                $this->procesarRegistroSellado($request, $proceso, $usuario);
            }  */

            $this->actualizarEstadoReservaYNotificar($request, $proceso, $usuario, $folio);

            if($request['id_estado'] == 6){
                $propiedad = Propiedad::find($request['folio'][0]['propiedad_id']);
                if($propiedad){
                    $propiedad->update([
                        'id_estado_alquiler' => $proceso->estado_alquiler_inicial,
                    ]);
                }
                if($proceso){
                    //busca el historial de estado dpto con el id mas grande
                    $historialEstadoDpto = Historial_estado_dpto::where('id_proceso_propiedad', $proceso->id)->orderByDesc('id')->first();
                    if($historialEstadoDpto){
                        $historialEstadoDpto->update([ 'id_estado' => '4']);
                    }
                }
            }

            return $proceso->fresh();
        });
    }

    /**
     * Actualiza los campos del proceso_propiedad afectados por el cambio de estado del contrato.
     */
    private function actualizarDatosProceso(Proceso_propiedad $proceso, array $request, $idHistorialEstadoContrato): void
    {
        $datosActualizar = ['id_historial_estado_contrato' => $idHistorialEstadoContrato];

        if (!empty($request['cant_meses'])) {
            $datosActualizar['meses_contrato'] = $request['cant_meses'];
        }

        if (($request['precio_alquiler'] ?? null) !== null) {
            $datosActualizar['precio_alquiler'] = $request['precio_alquiler'];
        }

        $proceso->update($datosActualizar);
    }

    /**
     * Obtiene el folio de la propiedad asociada al proceso (o 'N/D' si no existe).
     */
    private function obtenerFolioPropiedad($idPropiedad): string
    {
        $propiedad = Propiedad::where('id', $idPropiedad)->first();

        if (!$propiedad) {
            return 'N/D';
        }

        $empresaPropiedad = Empresas_propiedades::where('propiedad_id', $propiedad->id)->first();

        return $empresaPropiedad->folio ?? 'N/D';
    }

    /**
     * Determina si el request trae información suficiente para procesar el registro de sellado.
     */
    private function requiereProcesarSellado(array $request): bool
    {
        if (empty($request['folio'])) {
            return false;
        }

        $campos = ['monto', 'chojas', 'informe', 'CantInforme', 'contrato', 'inquilino_propietario', 'precio_alquiler'];

        foreach ($campos as $campo) {
            if (($request[$campo] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Crea o actualiza el registro de sellado en base al folio recibido.
     *
     * @throws \RuntimeException si el folio ya fue calculado previamente.
     */
    private function procesarRegistroSellado(array $request, Proceso_propiedad $proceso, ?Usuario $usuario): void
    {
        $folioSolicitado = $request['folio'][0]['folio'];
        $idEmpresa = $request['folio'][0]['empresa_id'];

        $idCasa = Propiedades_sys::where('carpeta', $folioSolicitado)->value('id_casa');

        $contrato = Contratos_cabecera_sys::where('id_casa', $idCasa)
            ->where('id_empresa', $idEmpresa)
            ->orderByDesc('id_contrato_cabecera')
            ->first(['id_inquilino', 'comienza']);

        $idInquilino = $contrato?->id_inquilino;
        $comienza = $contrato?->comienza;

        $nombreInquilino = $idInquilino
            ? Padron_sys::where('id_inquilino', $idInquilino)->value('razon_social')
            : '';

        $datosSellado = [
            'mostrar'                  => 0,
            'folio'                    => $folioSolicitado,
            'empresa'                  => $idEmpresa,
            'nombre'                   => $nombreInquilino ?? '',
            'cantidad_meses'           => $request['cant_meses'] ?? null,
            'monto_documento'          => $request['monto'] ?? null,
            'monto_contrato'           => $request['monto_contrato'] ?? null,
            'hojas'                    => $request['chojas'] ?? null,
            'informe'                  => $request['informe'] ?? null,
            'cantidad_informes'        => $request['CantInforme'] ?? null,
            'tipo_contrato'            => $request['tipo_contrato'] ?? null,
            'inq_prop'                 => $request['inquilino_propietario'] ?? null,
            'fecha_inicio'             => $comienza,
            'usuario_id'               => $usuario->id ?? null,
        ];

       
        $folioEncontrado = Registro_sellado::where('folio', $folioSolicitado)->first();

        if ($folioEncontrado) {
            if ($folioEncontrado->mostrar != 0) {
                /* throw new \RuntimeException('Folio ya calculado.'); */
                return;
            }
         

            return;
        }

        $registro = Registro_sellado::create($datosSellado);
        

        $proceso->update(['id_registro_sellado' => $registro->id_registro_sellado]);
     
    }

    /**
     * Actualiza el estado de la reserva asociada (según id_estado)
     * y dispara la notificación correspondiente al asesor.
     */
    private function actualizarEstadoReservaYNotificar(array $request, Proceso_propiedad $proceso, ?Usuario $usuario, string $folio): void
    {
        $mapaEstados = [
            8 => 2, // reserva -> cancelada/anulada
            9 => 1, // reserva -> reactivada
        ];

        $idEstado = $request['id_estado'] ?? null;

        if (!isset($mapaEstados[$idEstado])) {
            return;
        }

        Historial_estado_reserva::where('id', $proceso->id_historial_estado_reserva)
            ->update(['id_estado' => $mapaEstados[$idEstado]]);

        if (!$usuario) {
            return;
        }

        $mensaje = [
            'descripcion'       => "El folio {$folio} a cambiado de estado",
            'fecha'             => now()->isoFormat('DD/MM/YYYY'),
            'hora'              => now()->isoFormat('HH:mm'),
            'activo'            => 1,
            'usuarioNotificar'  => $proceso->asesor,
            'cliente_id'        => null,
            'id_criterio_venta' => null,
            'pertenece'         => 'reserva',
            'folio'             => $folio,
        ];

        $usuario->notify(new RecordatorioNotificacion($mensaje));
    }
}
