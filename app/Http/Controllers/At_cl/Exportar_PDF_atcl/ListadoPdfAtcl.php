<?php

namespace App\Http\Controllers\At_cl\Exportar_PDF_atcl;

use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Estado_alquiler;
use App\Models\At_cl\Observaciones_propiedades;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Propiedades_padron;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\At_cl\FiltrosPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\cliente\HistorialCodOfrecimiento;
use App\Models\cliente\HistorialCodMuestra;
use App\Models\cliente\HistorialCodigoConsulta;
use App\Models\cliente\CriterioBusquedaVenta;
use App\Models\cliente\Clientes;
use App\Models\At_cl\Tipo_inmueble;
use App\Services\At_cl\Exportar_PDF_atcl\PdfVentaService;

class ListadoPdfAtcl
{
    /**
     * Listado de propiedades unificado para Venta y Alquiler
     */
    public function listadoPropiedad(Request $request)
    {
        $informacionMostrar = $request->informacionMostrar;
        $pertenece = $request->pertenece;
        $username = '-';
        $sector = $request->sector;
        $contadorPropiedades = 0;

        if ($pertenece === 'listadoPropiedades') {

            // Usar el filtro unificado (el ordenamiento se aplica dentro, excepto precio)
            $query = (new FiltrosPdfService)->aplicarFiltrosUnificados($request->all());

            // Ejecutar la query
            $propiedades = $query->get();

            // Solo ordenar por precio si es necesario (post-query)
            if ($request->orden === 'precio_asc' || $request->orden === 'precio_desc') {
                $propiedades = (new FiltrosPdfService)->ordenarPorPrecio($propiedades, $request->orden, $sector);
            }

            // Obtener usernames
            $modifierIds = $propiedades->pluck('last_modified_by')->filter()->unique()->values();
            $usernamesById = $modifierIds->isNotEmpty()
                ? Usuario::whereIn('id', $modifierIds)->pluck('username', 'id')->all()
                : [];

            foreach ($propiedades as $propiedad) {
                $contadorPropiedades++;
                $modId = $propiedad->last_modified_by;
                $propiedad->username = ($modId && isset($usernamesById[$modId]))
                    ? $usernamesById[$modId]
                    : '-';
            }

            //Modificar captador_int
            foreach ($propiedades as $propiedad) {
                $usernameCaptador = $propiedad->captador_int ? Usuario::find($propiedad->captador_int)->username : '-';
                $propiedad->captador_int = $usernameCaptador;
            }

            //Modificar asesor
            foreach ($propiedades as $propiedad) {
                $usernameAsesor = $propiedad->asesor ? Usuario::find($propiedad->asesor)->username : '-';
                $propiedad->asesor = $usernameAsesor;
            }
            // Usuario actual
            $usuario_id = auth('api')->id();
            $authUser = $usuario_id ? Usuario::find($usuario_id) : null;
            $username = $authUser->username ?? '-';

            //Log::info('propiedades', [$propiedades]);

            // Generar HTML
            $html = view('pdfs.atcl.listadoPropiedad', compact('propiedades', 'username', 'informacionMostrar', 'pertenece', 'sector', 'contadorPropiedades'))->render();
        }
        if ($pertenece === 'estadoPropietario') {
            /*  $contadorPropietarios = 0; */
            //Log::info('entro a propietarios');
            $propietario = $request->propietario;
            //sLog::info('propietario', [$propietario]);
            $campoCodigo = ($sector === 'Alquiler') ? 'cod_alquiler' : 'cod_venta';

            if ($propietario !== null) {
                $propiedades = Propiedades_padron::where('padron_id', $propietario)
                    ->with([
                        'propiedad.propietarios',
                        'propiedad.fotos',
                        'propiedad.documentacion',
                        'propiedad.video',
                        'propiedad.calle',
                        'propiedad.folios.empresa',
                        'propiedad.tipoInmueble',
                        'propiedad.precio'
                    ])
                    ->get()
                    ->map(function ($pp) {
                        return $pp->propiedad;
                    })
                    ->filter(function ($propiedad) use ($campoCodigo) {
                        return $propiedad && !is_null($propiedad->$campoCodigo);
                    })
                    ->sortBy(function ($propiedad) {
                        return $propiedad->calle->name ?? '';
                    });

                foreach ($propiedades as $propiedad) {
                    $contadorPropiedades++;
                }
            } else {
                $propiedades = Propiedad::whereNotNull($campoCodigo)
                    ->with([
                        'fotos',
                        'documentacion',
                        'video',
                        'calle',
                        'zona',
                        'tipoInmueble',
                        'precio',
                        'folios.empresa',
                        'propietarios',
                    ])
                    ->get()
                    ->sortBy(function ($propiedad) {
                        return $propiedad->calle->name ?? '';
                    });

                foreach ($propiedades as $propiedad) {
                    $contadorPropiedades++;
                }
            }

            //Log::info('eeee', [$propiedades]);
            $html = view('pdfs.atcl.listadoPropiedad', compact('propiedades', 'username', 'pertenece', 'sector', 'contadorPropiedades'))->render();
        }
        if ($pertenece === 'ofrecimientoVenta') {
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');

            if ($fechaDesde && $fechaHasta) {
                $fechaDesde .= ' 00:00:00';
                $fechaHasta .= ' 23:59:59';
                $filtroFechaConsulta = "AND fecha_hora BETWEEN ? AND ?";
                $parametros = [
                    $fechaDesde,
                    $fechaHasta,
                    $fechaDesde,
                    $fechaHasta,
                    $fechaDesde,
                    $fechaHasta,
                ];
            } else {
                $filtroFechaConsulta = ''; // sin filtro
                $parametros = [];
            }

            $sql = "SELECT
                        p.cod_venta,
                        p.id_calle,
                        p.numero_calle,
                        p.piso,
                        p.departamento,
                        (SELECT COUNT(*) FROM sistema_clientes.historial_cod_consulta
                          WHERE codigo_consulta = p.cod_venta $filtroFechaConsulta) as total_consultas,

                        (SELECT COUNT(*) FROM sistema_clientes.historial_cod_muestra
                          WHERE codigo_muestra = p.cod_venta $filtroFechaConsulta) as total_muestras,

                        (SELECT COUNT(*) FROM sistema_clientes.historial_cod_ofrecimiento
                          WHERE codigo_ofrecimiento = p.cod_venta $filtroFechaConsulta) as total_ofrecimientos,
                        c.name as calle
                    FROM propiedades p
                    INNER JOIN calle c ON p.id_calle = c.id
                    WHERE p.cod_venta IS NOT NULL
                    ORDER BY p.cod_venta ASC";

            $query = DB::connection('mysql')->select($sql, $parametros);
            $consultaTotal = 0;
            $muestraTotal = 0;
            $ofrecimientoTotal = 0;
            foreach ($query as $q) {
                if ($q->total_consultas >= 1) {
                    $consultaTotal++;
                }
                if ($q->total_muestras >= 1) {
                    $muestraTotal++;
                }
                if ($q->total_ofrecimientos >= 1) {
                    $ofrecimientoTotal++;
                }
            }
            //Log::info('consultaTotal', [$consultaTotal]);
            ////Log::info('muestraTotal', [$muestraTotal]);
            // Log::info('ofrecimientoTotal', [$ofrecimientoTotal]);

            //Log::info('query', [$query]);
            //dd($query);

            $html = view('pdfs.atcl.listadoPropiedad', compact('query', 'username', 'pertenece', 'sector', 'consultaTotal', 'muestraTotal', 'ofrecimientoTotal'))->render();
        }
        if ($pertenece === 'devoluciones') {
            $codigo = $request->codigo;
            $datosOfrecimiento = HistorialCodOfrecimiento::where('codigo_ofrecimiento', $codigo)->get()
                ->map(function ($item) {
                    $item->referencia = 'Ofrecimiento';
                    return $item;
                });
            $datosMuestra = HistorialCodMuestra::where('codigo_muestra', $codigo)->get()
                ->map(function ($item) {
                    $item->referencia = 'Muestra';
                    return $item;
                });
            $datosConsulta = HistorialCodigoConsulta::where('codigo_consulta', $codigo)->get()
                ->map(function ($item) {
                    $item->referencia = 'Consulta';
                    return $item;
                });

            $datosTotales = $datosOfrecimiento->merge($datosMuestra)->merge($datosConsulta)->sortBy('fecha_hora');
            foreach ($datosTotales as $item) {
                $item->criterio_busqueda = CriterioBusquedaVenta::where('id_criterio_venta', $item->id_criterio_venta)->first();
                $item->cliente = Clientes::where('id_cliente', $item->criterio_busqueda->id_cliente)->first();
                $item->nombre_usuario = Usuario::where('id', $item->cliente->id_asesor_venta)->first()->username;
            }
            if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                $fechaDesde = $request->input('fecha_desde') . ' 00:00:00';
                $fechaHasta = $request->input('fecha_hasta') . ' 23:59:59';

                $datosTotales = $datosTotales->filter(function ($item) use ($fechaDesde, $fechaHasta) {
                    return $item->fecha_hora >= $fechaDesde && $item->fecha_hora <= $fechaHasta;
                });
            }
            //Log::info('datosTotales', [$datosTotales]);
            //dd($datosTotales);
            $html = view('pdfs.atcl.listadoPropiedad', compact('datosTotales', 'username', 'pertenece', 'sector'))->render();
        }
        if ($pertenece === 'criteriosActivos') {
            $query = CriterioBusquedaVenta::where('estado_criterio_venta', 'Activo')
                ->whereHas('cliente', function ($q) use ($request) {
                    $q->where('id_asesor_venta', $request->asesor_id);
                })
                ->with(['tipoInmueble', 'zona', 'cliente']);

            if (!empty($request->zona_id)) {
                $query->whereIn('id_zona', $request->input('zona_id'));
            }
            if (!empty($request->tipo)) {
                $query->whereIn('id_tipo_inmueble', $request->input('tipo'));
            }
            if ($request->has('cantidad_dormitorios') && $request->input('cantidad_dormitorios') != null) {
                $query->where('cant_dormitorios', $request->input('cantidad_dormitorios'));
            }

            if ($request->has('estado') && $request->input('estado') != null) {
                $query->where('id_categoria', $request->input('estado'));
            }

            $precioMin = $request->input('precio_minimo');
            $precioMax = $request->input('precio_maximo');


            if ($precioMin && $precioMax) {
                $query->whereBetween('precio_hasta', [$precioMin, $precioMax]);
            } elseif ($precioMin) {
                $query->where('precio_hasta', '>=', $precioMin);
            } elseif ($precioMax) {
                $query->where('precio_hasta', '<=', $precioMax);
            }

            $criterios_vendedor = $query->with(['tipoInmueble', 'zona'])->orderBy('id_categoria', 'desc')->get();
            $html = view('pdfs.atcl.listadoPropiedad', compact('criterios_vendedor', 'username', 'pertenece', 'sector'))->render();
        }
        if ($pertenece === 'consultasIngresadas') {
            $data = CriterioBusquedaVenta::query()
                ->where('estado_criterio_venta', 'Activo')
                ->with(['tipoInmueble', 'zona', 'cliente.asesor.usuario', 'historialConsultas']);

            $fechaDesde = $request->desde;
            $fechaHasta = $request->hasta;

            if (!empty($fechaDesde) && !empty($fechaHasta)) {

                $data->whereBetween('fecha_criterio_venta', [$fechaDesde, $fechaHasta]);
            } elseif (!empty($fechaDesde)) {

                $data->where('fecha_criterio_venta', '>=', $fechaDesde);
            } elseif (!empty($fechaHasta)) {

                $data->where('fecha_criterio_venta', '<=', $fechaHasta);
            }

            $data = $data->orderBy('id_categoria', 'desc')->get();


            // 1. Contamos el total de criterios directamente usando el método count() de la colección
            $total_criterios = $data->count();

            // 2. Agrupamos y contamos cuántos criterios tiene cada asesor
            $conteoAsesores = [];

            // Agrupamos la colección por el username del asesor de forma segura
            $agrupadosPorAsesor = $data->groupBy(function ($criterio) {
                return $criterio->cliente->asesor->usuario->username ?? 'Sin Asesor';
            });

            foreach ($agrupadosPorAsesor as $username => $criterios) {
                $conteoAsesores[$username] = $criterios->count();
            }
            //Log::info($data);

            // 3. Agrupamos y contamos cuántos criterios tiene cada tipo de ingreso (Whatsapp, Sitio web, etc)
            $total_tipo_ingreso = [];
            /* no distinguir entre mayuculas y minisculas */
            $agrupadosPorIngreso = $data->groupBy(function ($criterio) {
                return strtolower($criterio->cliente?->ingreso ?? 'Sin Especificar');
            });

            foreach ($agrupadosPorIngreso as $ingreso => $criterios) {
                $total_tipo_ingreso[$ingreso] = $criterios->count();
            }

            $html = view('pdfs.atcl.listadoPropiedad', compact('data', 'username', 'pertenece', 'sector', 'total_criterios', 'conteoAsesores', 'total_tipo_ingreso'))->render();
        }
        if ($pertenece === 'conversaciones') {
            //  Log::info($request->all());
            /* $clientes = $this->pdfVentaService->ObtenerClientesAsesor($request->asesor); */
            $clientes = (new PdfVentaService())->ObtenerClientesAsesor($request->asesor_id);
            // Log::info($clientes);
            /*  $historialConversacion = $this->pdfVentaService->ObtenerHistorialConversacion($clientes); */
            $historialConversacion = (new PdfVentaService())->ObtenerHistorialConversacion($clientes);
            //Log::info($historialConversacion);
            /* $datosTotales = $this->pdfVentaService->CombinarDatos($clientes, $historialConversacion); */
            $datosTotales = (new PdfVentaService())->CombinarDatos($clientes, $historialConversacion);

            $html = view('pdfs.atcl.listadoPropiedad', compact('datosTotales', 'pertenece', 'sector'))->render();
        }
        if ($pertenece === 'informeNovedades') {
            // Primero, obtenemos las observaciones tipo 'A' aplicando filtros de fecha si existieran
            $obsQuery = Observaciones_propiedades::with([
                'propiedad.folios.empresa',
                'propiedad.tipoInmueble',
                'propiedad.estadoAlquiler',
                'propiedad.calle',
                'propiedad.zona',
                'propiedad.precio',
            ])->where('tipo_ofera', 'A');

            if ($request->filled('desde') && $request->filled('hasta')) {
                $fechaDesde = $request->input('desde') . ' 00:00:00';
                $fechaHasta = $request->input('hasta') . ' 23:59:59';

                $obsQuery->where('updated_at', '>=', $fechaDesde)
                    ->where('updated_at', '<=', $fechaHasta);
            } elseif ($request->filled('desde')) {
                $fechaDesde = $request->input('desde') . ' 00:00:00';
                $obsQuery->where('updated_at', '>=', $fechaDesde);
            } elseif ($request->filled('hasta')) {
                $fechaHasta = $request->input('hasta') . ' 23:59:59';
                $obsQuery->where('updated_at', '<=', $fechaHasta);
            }

            $observaciones = $obsQuery->get();
            $data = $observaciones->groupBy('propiedad_id');

            // Ahora buscamos propiedades que hayan sido actualizadas en el mismo rango
            $propQuery = Propiedad::with([
                'folios.empresa',
                'tipoInmueble',
                'estadoAlquiler',
                'calle',
                'zona',
                'precio',
            ]);

            if (isset($fechaDesde) && isset($fechaHasta)) {
                $propQuery->where('updated_at', '>=', $fechaDesde)
                    ->where('updated_at', '<=', $fechaHasta);
            } elseif (isset($fechaDesde)) {
                $propQuery->where('updated_at', '>=', $fechaDesde);
            } elseif (isset($fechaHasta)) {
                $propQuery->where('updated_at', '<=', $fechaHasta);
            }

            $propiedadesActualizadas = $propQuery->get();

            foreach ($propiedadesActualizadas as $propiedad) {
                $propId = $propiedad->id;
                // Si no hay observaciones para esta propiedad, añadimos una entrada sintética
                if (!$data->has($propId) || $data[$propId]->isEmpty()) {
                    $sintetica = (object) [
                        'id' => null,
                        'propiedad_id' => $propId,
                        'propiedad' => $propiedad,
                        'updated_at' => $propiedad->updated_at,
                        'notes' => 'Propiedad modificada sin observación',
                    ];

                    $data[$propId] = collect([$sintetica]);
                }
            }

            $fechas = [$request->input('desde'), $request->input('hasta')];
            //Log::info('data (informeNovedades combinado)', [$data]);

            $html = view('pdfs.atcl.listadoPropiedad', compact('data', 'username', 'pertenece', 'sector', 'fechas'))->render();
        }

        $orientacion = 'landscape';
        if ($pertenece === 'ofrecimientoVenta' || $pertenece === 'conversaciones' || $pertenece === 'informeNovedades') {
            $orientacion = 'portrait';
        }




        return response()->streamDownload(function () use ($html, $username, $orientacion) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('legal')
                ->margins(10, 1, 10, 1)
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', true)
                ->setOption('headerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Ficha de Propiedad</span><span style="text-align:right;">Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
                ->$orientacion()
                ->setOption('footerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:center;">' . $username . '</span>  <span style="text-align:right;" class="date"></span></div>')
                ->pdf();
        }, 'ficha_propiedad.pdf');
    }
}
