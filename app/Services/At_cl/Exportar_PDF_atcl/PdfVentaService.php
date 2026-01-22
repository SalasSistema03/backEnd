<?php
// app/Services/Exportar_PDF_atcl/PdfVentaService.php

namespace App\Services\At_cl\Exportar_PDF_atcl;

use App\Models\cliente\HistorialCriteriosConversacion;
use App\Models\cliente\HistorialCodOfrecimiento;
use App\Models\cliente\HistorialCodMuestra;
use App\Models\cliente\HistorialCodigoConsulta;
use App\Models\cliente\Clientes;
use Illuminate\Support\Facades\DB;
use App\Models\At_cl\Propiedades_padron;
use App\Models\At_cl\Propiedad;

class PdfVentaService
{
    //Funcion que permite obtener los datos del cliente junto con su criterio
    public function ObtenerClientesAsesor($id_asesor)
    {
        // 1. Obtener los clientes con criterios
        $clientes = Clientes::where('id_asesor_venta', $id_asesor)
            ->join('criterio_busqueda_venta', 'clientes.id_cliente', 'criterio_busqueda_venta.id_cliente')
            ->where('criterio_busqueda_venta.estado_criterio_venta', 'Activo')
            ->select(
                'clientes.id_cliente',
                'clientes.nombre',
                'clientes.telefono',
                'clientes.nombre_de_inmobiliaria',
                'criterio_busqueda_venta.id_criterio_venta',
                'criterio_busqueda_venta.id_tipo_inmueble',
                'criterio_busqueda_venta.cant_dormitorios',
                'criterio_busqueda_venta.precio_hasta',
                'criterio_busqueda_venta.fecha_criterio_venta'
            )
            ->get();

        // 2. Obtener los tipos de inmueble desde la otra base (mysql)
        $tiposInmueble = DB::connection('mysql')
            ->table('tipo_inmueble')
            ->pluck('inmueble', 'id'); // [id_tipo_inmueble => 'Casa', 'Depto', etc.]

        // 3. Reemplazar el id_tipo_inmueble por el nombre
        $clientes->transform(function ($cliente) use ($tiposInmueble) {
            $cliente->inmueble = $tiposInmueble[$cliente->id_tipo_inmueble] ?? 'Sin definir';
            unset($cliente->id_tipo_inmueble); // opcional: si no querés mostrar el ID
            return $cliente;
        });

        return $clientes;
    }

    //Funcion que permite obtener todas las conversaciones del cliente
    public function ObtenerHistorialConversacion($id_criterio_venta)
    {

        // 2. Obtener los historiales de cada tipo
        $criterioIds = $id_criterio_venta->pluck('id_criterio_venta')->unique();

        $conversaciones = HistorialCriteriosConversacion::whereIn('id_criterio_venta', $criterioIds)
            ->get()
            ->groupBy('id_criterio_venta');

        $ofrecimientos = HistorialCodOfrecimiento::whereIn('id_criterio_venta', $criterioIds)
            ->get()
            ->groupBy('id_criterio_venta');

        $muestras = HistorialCodMuestra::whereIn('id_criterio_venta', $criterioIds)
            ->get()
            ->groupBy('id_criterio_venta');

        $consultas = HistorialCodigoConsulta::whereIn('id_criterio_venta', $criterioIds)
            ->get()
            ->groupBy('id_criterio_venta');

        return [
            'conversaciones' => $conversaciones,
            'ofrecimientos' => $ofrecimientos,
            'muestras' => $muestras,
            'consultas' => $consultas
        ];
    }

    //Funcion que permite combinar los datos del cliente junto con los datos de su conversacion 
    public function CombinarDatos($clientes, $historial)
    {
        // 3. Combinar los datos
        $datosTotales = $clientes->map(function ($cliente) use ($historial) {
            $idCriterio = $cliente->id_criterio_venta;

            // Obtener cada historial individual
            $conversaciones = isset($historial['conversaciones'])
                ? $historial['conversaciones']->get($idCriterio, collect())
                : collect();

            $ofrecimientos = isset($historial['ofrecimientos'])
                ? $historial['ofrecimientos']->get($idCriterio, collect())
                : collect();

            $muestras = isset($historial['muestras'])
                ? $historial['muestras']->get($idCriterio, collect())
                : collect();

            $consultas = isset($historial['consultas'])
                ? $historial['consultas']->get($idCriterio, collect())
                : collect();

            // 🔹 (Opcional) marcar tipo para cada elemento
            $conversaciones = $conversaciones->map(function ($item) {
                $item->tipo = 'conversación';
                return $item;
            });

            $ofrecimientos = $ofrecimientos->map(function ($item) {
                $item->tipo = 'ofrecimiento';
                return $item;
            });

            $muestras = $muestras->map(function ($item) {
                $item->tipo = 'muestra';
                return $item;
            });

            $consultas = $consultas->map(function ($item) {
                $item->tipo = 'consulta';
                return $item;
            });

            // 🔹 Merge y ordenar por fecha_hora
            $historialTotal = $conversaciones
                ->merge($ofrecimientos)
                ->merge($muestras)
                ->merge($consultas)
                ->sortBy('fecha_hora')
                ->values();

            // Retornar toda la info del cliente
            return [
                'cliente' => $cliente,
                'conversaciones' => $conversaciones,
                'ofrecimientos' => $ofrecimientos,
                'muestras' => $muestras,
                'consultas' => $consultas,
                'historial_total' => $historialTotal, // 👈 unificado y ordenado
            ];
        })->groupBy('cliente.id_cliente');

        return $datosTotales;
    }

    public function ObtenerPropiedadesPropietarios(?int $propietarioId)
    {
        if ($propietarioId) {
            // Buscar propiedades por propietario
            return Propiedades_padron::where('padron_id', $propietarioId)
                ->with([
                    'propiedad.propietarios',
                    'propiedad.fotos',
                    'propiedad.video',
                    'propiedad.documentacion'
                ])
                ->get()
                ->map(function ($pp) {
                    return $pp->propiedad;
                })
                ->filter(function ($propiedad) {
                    return $propiedad && $propiedad->cod_venta;
                })
                ->values(); // opcional: limpia keys
        }

        // Si no hay propietario, traer todas las propiedades con cod_venta
        return Propiedad::whereNotNull('cod_venta')
            ->with([
                'fotos',
                'documentacion',
                'calle',
                'zona',
                'tipoInmueble',
                'precio',
                'estadoVenta'
            ])
            ->get();
    }
}
