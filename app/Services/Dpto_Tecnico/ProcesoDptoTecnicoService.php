<?php

namespace App\Services\Dpto_Tecnico;

use App\Models\At_cl\Propiedad;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Proceso_propiedad;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\cliente\Usuario_sector;
use App\Notifications\RecordatorioNotificacion;
use App\Models\proceso\Historial_estado_dpto;
use App\Models\proceso\Estado_dpto;


class ProcesoDptoTecnicoService
{
    public function getHistorialInventario($form)
{
    $query = Proceso_propiedad::with([
        'propiedad.folios',
        'cliente',
        'asesorUsuario',
        'historialEstadoReserva',
        'historialEstadoContrato.estado',
        /* 'historialEstadoContrato.tirillaEntregadaPor',
        'historialEstadoContrato.tirillaControladaPor', */
        'historialEstadoDpto.quien_cargo',
        'historialEstadoDpto.estado',
        'historialEstadoDpto.verificado_por',
        'propiedad.calle',
        'registroSellado',
        'historialEstadoDpto',
    ])->whereNotNull('id_historial_estado_dpto');

    if (!empty($form['mes']) && !empty($form['anio'])) {
        $query->whereYear('fecha_reserva', $form['anio'])
            ->whereMonth('fecha_reserva', $form['mes']);
    }

    if (!empty($form['folio'])) {
        $propiedadIds = Propiedad::whereHas('folios', function ($q) use ($form) {
            $q->where('folio', $form['folio']);
        })->pluck('id');

        $query->whereIn('id_propiedad', $propiedadIds);
    }

    if (!empty($form['filtroAsesor'])) {
        $query->whereHas('historialEstadoDpto', function ($q) use ($form) {
            $q->where('verificado_por', $form['filtroAsesor']);
        });
    }

    if (!empty($form['filtroEstado'])) {
        $query->whereHas('historialEstadoDpto', function ($q) use ($form) {
            $q->where('id_estado', $form['filtroEstado']);
        });
    } else {
        // No se filtró por estado -> ordenar por id_estado de historialEstadoDpto
        $query->join(
                'historial_estado_dpto',
                'proceso_propiedad.id_historial_estado_dpto',
                '=',
                'historial_estado_dpto.id'
            )
            ->select('proceso_propiedad.*')
            ->orderBy('historial_estado_dpto.id_estado', 'asc');
    }

    //Log::info($query->get());
    return $query->get();
}

    public function getUsuariosDpto(Request $request)
    {
        $usuarios = Usuario_sector::with('usuario')->where('dpto', 'S')->get();

        return $usuarios;
    }

    public function getEstadoDpto()
    {
        $estados = Estado_dpto::all();

        return $estados;
    }

    public function actualizarInventario(Request $request, $usuarioId)
    {
       /*  Log::info([$request->all()]);
        dd($request->all()); */
        $data = Historial_estado_dpto::find($request->inventario_id);

        if ($data) {
            $dataNuevo = Historial_estado_dpto::create([
                'id_estado'             => $request->estado_id,
                'observaciones'         => $request->observaciones ?? 'Modificacion General',
                'fecha_inventario'      => \Carbon\Carbon::parse($request->fecha_inventario)->setTimeFrom(now()),
                'fecha_carga'           => now(),
                'quien_cargo'           => $usuarioId,
                'id_proceso_propiedad'  => $request->id_proceso_propiedad,
                'verificado_por'        => $request->verificado_por,
            ]);

            $procesoPropiedad = Proceso_propiedad::find($request->id_proceso_propiedad);
           
            $procesoPropiedad->update([
                'id_historial_estado_dpto' => $dataNuevo->id
            ]);

            if($request->estado_id == 4){
                $procesoPropiedad->propiedad->update([
                    'id_estado_alquiler' => $procesoPropiedad->estado_alquiler_inicial,
                ]);
                $historialEstadoContrato = Historial_estado_contrato::where('id_proceso_propiedad', $procesoPropiedad->id)->orderByDesc('id')->first();
                if($historialEstadoContrato){
                    $historialEstadoContrato->update([ 'id_estado' => '6']);
                }
            }


            return response()->json([
                'message' => 'Inventario actualizado exitosamente.',
                'data' => $data,
            ], 200);
        }


        return response()->json([
            'message' => 'Inventario no encontrado.',
        ], 404);
    }

    public function getComentarioInventario($id_inventario)
    {
        return Historial_estado_dpto::where('id_proceso_propiedad', $id_inventario)->with('quien_cargo:id,username', 'estado')->get();
    }
}
