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
        //Log::info('form recibida:', ['form' => $form]);

        // Eager load all relations including historialEstadoContrato and its state (estado)
        $query = Proceso_propiedad::with([
            'propiedad.folios',
            'cliente',
            'asesorUsuario',
            'historialEstadoReserva',
            'historialEstadoContrato.estado',
            'propiedad.calle',
        ])->whereNotNull('id_historial_estado_contrato');

        // Diagnostic log: let's query the dates and IDs of all records that have a contract history
        $todos = Proceso_propiedad::whereNotNull('id_historial_estado_contrato')
            ->get();

        /* Log::info('Registros existentes en DB:', [
            'total' => $todos->count(),
            'valores' => $todos->map(function($item) {
                return [
                    'id' => $item->id,
                    'fecha_reserva' => $item->fecha_reserva,
                    'id_historial_estado_contrato' => $item->id_historial_estado_contrato
                ];
            })->toArray()
        ]); */

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
        //Log::info('Resultados filtrados:', ['count' => $res->count()]);
        return $res;
    }
}
