<?php

namespace App\Services\contrato;

use App\Models\At_cl\Propiedad;
use App\Models\proceso\Estado_contrato;
use App\Models\proceso\Historial_estado_contrato;
use App\Models\proceso\Proceso_propiedad;
use App\Models\proceso\Historial_estado_reserva;
use App\Models\usuarios_y_permisos\Usuario;
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
            'propiedad.calle',
        ])->whereNotNull('id_historial_estado_contrato');

        $todos = Proceso_propiedad::whereNotNull('id_historial_estado_contrato')
            ->get();

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

        Log::info($res);
        //dd($res);
        //Log::info('Resultados filtrados:', ['count' => $res->count()]);
        return $res;
    }

    public function crearHistorialEstadoContrato(array $request)
    {



        $data = historial_estado_contrato::create([
            'id_estado' => $request['id_estado'] ?? null,
            'fecha_comercial_presenta_carpeta' => $request['fecha_comercial_presenta_carpeta'] ?? null,
            'fecha_preaprobada' => $request['fecha_preaprobada'] ?? null,
            'fecha_reserva' => $request['fecha_reserva'] ?? null,
            'gastos_administrativos' => $request['gastos_administrativos'] ?? null,
            'tirilla_entregada_a' => $request['tirilla_entregada_a'] ?? null,
            'fecha_tirilla_entregada' => $request['fecha_tirilla_entregada'] ?? null,
            'tirilla_controlada_por' => is_array($request['tirilla_controlada_por'] ?? null) ? $request['tirilla_controlada_por']['id'] : ($request['tirilla_controlada_por'] ?? null),
            'fecha_tirilla_controlada' => $request['fecha_tirilla_controlada'] ?? null,
            'fecha_contrato' => $request['fecha_contrato'] ?? null,
            'fecha_autorizacion' => $request['fecha_autorizacion'] ?? null,
            'fecha_finalizacion_firma_cobro' => $request['fecha_finalizacion_firma_cobro'] ?? null,
            'observaciones' => $request['observaciones'] ?? null,
            'fecha_inventario' => $request['fecha_inventario'] ?? null,
        ]);

        return $data;
    }
}
