<?php

namespace App\Http\Controllers\At_cl\Exportar_PDF_atcl;

use App\Models\At_cl\Empresas_propiedades;
use App\Models\At_cl\Estado_alquiler;
use App\Models\At_cl\Observaciones_propiedades;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Propiedades_padron;
use App\Models\At_cl\Tasaciones;
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
use Illuminate\Support\Carbon;

class ListadoPdfAtcl
{
    /**
     * Genera el PDF de los listados de Venta y Alquiler.
     *
     * El campo "pertenece" del request decide qué listado se arma; cada rama
     * prepara sus datos y los pasa a la vista pdfs.atcl.listadoPropiedad.
     * El resultado se devuelve como descarga de PDF generado con Browsershot.
     *
     * @param  \Illuminate\Http\Request  $request  Filtros del listado; incluye "pertenece" y "sector".
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function listadoPropiedad(Request $request)
    {
        $informacionMostrar = $request->informacionMostrar;
        $pertenece = $request->pertenece;
        $username = '-';
        $sector = $request->sector;
        $contadorPropiedades = 0;
        $filtrosService = new FiltrosPdfService;

        if ($pertenece === 'listadoPropiedades') {

            // Filtros comunes del listado; el ordenamiento por precio y autorización se aplica después de la consulta.
            $query = $filtrosService->aplicarFiltrosUnificados($request->all());

            // Carga las relaciones que usa la vista en la misma consulta.
            $propiedades = $query->with(['observacionesPropiedades', 'tipoInmueble', 'historialEstadosAlquiler'])->get();

            // El precio se ordena sobre la colección ya cargada.
            if ($request->orden === 'precio_asc' || $request->orden === 'precio_desc') {
                $propiedades = $filtrosService->ordenarPorPrecio($propiedades, $request->orden, $sector);
            }

            if ($request->orden === 'autorizacion') {
                $propiedades = $filtrosService->ordenarPorAutorizacion($propiedades, $request->orden, $sector);
            }

            $contadorPropiedades = $propiedades->count();

            // Reúne los IDs de usuario (modificador, captadores y asesor) para resolver sus nombres con una sola consulta.
            $modifierIds  = $propiedades->pluck('last_modified_by')->filter();
            $captadorVIds = $propiedades->pluck('captador_int_v')->filter();
            $captadorAIds = $propiedades->pluck('captador_int_a')->filter();
            $asesorIds    = $propiedades->pluck('asesor')->filter();

            $todosLosIds = $modifierIds
                ->merge($captadorVIds)
                ->merge($captadorAIds)
                ->merge($asesorIds)
                ->unique()
                ->values();

            // Un único SELECT de usernames indexados por id.
            $usernamesById = $todosLosIds->isNotEmpty()
                ? Usuario::whereIn('id', $todosLosIds)->pluck('username', 'id')->all()
                : [];

            // Reemplaza cada ID por su username; si el ID es nulo o no existe en el mapa, se muestra '-'.
            foreach ($propiedades as $propiedad) {
                $modId = $propiedad->last_modified_by;
                $propiedad->username = ($modId && isset($usernamesById[$modId]))
                    ? $usernamesById[$modId]
                    : '-';

                $propiedad->captador_int_v = ($propiedad->captador_int_v && isset($usernamesById[$propiedad->captador_int_v]))
                    ? $usernamesById[$propiedad->captador_int_v]
                    : '-';

                $propiedad->captador_int_a = ($propiedad->captador_int_a && isset($usernamesById[$propiedad->captador_int_a]))
                    ? $usernamesById[$propiedad->captador_int_a]
                    : '-';

                $propiedad->asesor = ($propiedad->asesor && isset($usernamesById[$propiedad->asesor]))
                    ? $usernamesById[$propiedad->asesor]
                    : '-';


                if ($request->estado_id === 1 || $request->estado_id === 2) {


                    $historial = $propiedad->historialEstadosAlquiler;

                    if ($historial && in_array($historial->id_estado_alquiler, [1, 2])) {
                        $propiedad->fecha_antiguedad = Carbon::parse($historial->fecha_alquiler);
                        $propiedad->antiguedad = $this->formatearAntiguedad($historial->fecha_alquiler);
                    } else {
                        $propiedad->fecha_antiguedad = Carbon::parse($propiedad->created_at);
                        $propiedad->antiguedad = $this->formatearAntiguedad($propiedad->created_at);
                    }
                } else {

                    $propiedad->antiguedad = "-";
                }
            }

            // Usuario autenticado; su username aparece en el pie del PDF.
            $usuario_id = auth('api')->id();
            $authUser = $usuario_id ? Usuario::find($usuario_id) : null;
            $username = $authUser->username ?? '-';

            $conteoPorTipo = $propiedades
                ->groupBy(function ($propiedad) {
                    // El nombre de la relación en el modelo Propiedad es tipoInmueble (en camelCase)
                    return $propiedad->tipoInmueble->inmueble ?? 'SIN TIPO';
                })
                ->map(function ($grupo) {
                    return $grupo->count();
                });

            // Cantidad por tipo de inmueble, p. ej. ['DEPARTAMENTO' => 5, 'CASA' => 3].
            $conteoPorTipoArray = $conteoPorTipo->toArray();

            // Renderiza la vista del PDF con los datos preparados.
            $html = view('pdfs.atcl.listadoPropiedad', compact(
                'propiedades',
                'username',
                'informacionMostrar',
                'pertenece',
                'sector',
                'contadorPropiedades',
                'conteoPorTipoArray'
            ))->render();
        }
        if ($pertenece === 'estadoPropietario') {
            $propietario = $request->propietario;
            // El código que identifica la propiedad depende del sector: cod_alquiler o cod_venta.
            $campoCodigo = ($sector === 'Alquiler') ? 'cod_alquiler' : 'cod_venta';

            // Con propietario seleccionado se listan sus propiedades a través del padrón;
            // sin propietario se listan todas las propiedades que tienen código en el sector.
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
                        'propiedad.precio',
                        'propiedad.estadoAlquiler',
                        'propiedad.estadoVenta',
                    ])
                    ->get()
                    ->map(function ($pp) {
                        return $pp->propiedad;
                    })
                    ->filter(function ($propiedad) use ($campoCodigo) {
                        return $propiedad && !is_null($propiedad->$campoCodigo);
                    });
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
                        'estadoAlquiler',
                        'estadoVenta',
                    ])
                    ->get();
            }

            // Ordenamiento elegido en el formulario; sin opción válida se ordena por calle.
            $orden = $request->orden;
            if ($orden === 'precio_asc' || $orden === 'precio_desc') {
                $filtrosService = new FiltrosPdfService;
                $propiedades = $filtrosService->ordenarPorPrecio($propiedades, $orden, $sector);
            } elseif ($orden === 'estado') {
                $propiedades = $propiedades->sortBy(function ($propiedad) use ($sector) {
                    return ($sector === 'Alquiler') ? ($propiedad->estadoAlquiler->name ?? '') : ($propiedad->estadoVenta->name ?? '');
                });
            } elseif ($orden === 'tipo') {
                $propiedades = $propiedades->sortBy(function ($propiedad) {
                    return $propiedad->tipoInmueble->name ?? '';
                });
            } elseif ($orden === 'zona') {
                $propiedades = $propiedades->sortBy(function ($propiedad) {
                    return $propiedad->zona->name ?? '';
                });
            } /* elseif ($orden === 'codigo') {
                $propiedades = $propiedades->sortBy(function ($propiedad) use ($campoCodigo) {
                    return $propiedad->$campoCodigo ?? 0;
                });
            } */ else {
                // Orden por defecto: calle.
                $propiedades = $propiedades->sortBy(function ($propiedad) {
                    return $propiedad->calle->name ?? '';
                });
            }

            foreach ($propiedades as $propiedad) {
                $contadorPropiedades++;
            }

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
                $filtroFechaConsulta = ''; // Sin rango: se cuentan todos los registros históricos.
                $parametros = [];
            }

            // Cada subconsulta cuenta, por código de venta, los registros de su tabla de historial (consultas,
            // muestras y ofrecimientos). El par de fechas se repite una vez por subconsulta, por eso $parametros
            // contiene seis valores.

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


            $html = view('pdfs.atcl.listadoPropiedad', compact('query', 'username', 'pertenece', 'sector', 'consultaTotal', 'muestraTotal', 'ofrecimientoTotal'))->render();
        }
        if ($pertenece === 'devoluciones') {
            // Reúne los movimientos del código en sus tres historiales (ofrecimiento, muestra y consulta).
            // Cada registro se etiqueta con su origen en la propiedad "referencia".
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
            // Para cada movimiento se busca el criterio de búsqueda, su cliente y el username del asesor del cliente.
            foreach ($datosTotales as $item) {
                $item->criterio_busqueda = CriterioBusquedaVenta::where('id_criterio_venta', $item->id_criterio_venta)->first();
                $item->cliente = Clientes::where('id_cliente', $item->criterio_busqueda->id_cliente)->first();
                $item->nombre_usuario = Usuario::where('id', $item->cliente->id_asesor_venta)->first()->username;
            }
            // El rango de fechas se aplica después de cargar los movimientos, comparando fecha_hora como texto.
            if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                $fechaDesde = $request->input('fecha_desde') . ' 00:00:00';
                $fechaHasta = $request->input('fecha_hasta') . ' 23:59:59';

                $datosTotales = $datosTotales->filter(function ($item) use ($fechaDesde, $fechaHasta) {
                    return $item->fecha_hora >= $fechaDesde && $item->fecha_hora <= $fechaHasta;
                });
            }
            $html = view('pdfs.atcl.listadoPropiedad', compact('datosTotales', 'username', 'pertenece', 'sector'))->render();
        }
        if ($pertenece === 'consultasIngresadas') {

            // Consultas Nuevas: solo los clientes cuya primera consulta histórica cae dentro del rango de fechas.
            if ($request->consulta === 'Consultas Nuevas') {

                $data = CriterioBusquedaVenta::query()
                    ->with([
                        'tipoInmueble',
                        'zona',
                        'cliente.asesor.usuario',
                        'historialConsultas'
                    ]);

                if (!empty($request->asesor)) {
                    $data->whereHas('cliente', function ($query) use ($request) {
                        $query->where('id_asesor_venta', $request->asesor);
                    });
                }

                $fechaDesde = $request->desde;
                $fechaHasta = $request->hasta;

                if (!empty($fechaDesde) && !empty($fechaHasta)) {

                    $data->whereBetween(
                        'fecha_criterio_venta',
                        [$fechaDesde, $fechaHasta]
                    );
                } elseif (!empty($fechaDesde)) {

                    $data->where(
                        'fecha_criterio_venta',
                        '>=',
                        $fechaDesde
                    );
                } elseif (!empty($fechaHasta)) {

                    $data->where(
                        'fecha_criterio_venta',
                        '<=',
                        $fechaHasta
                    );
                }

                $data = $data
                    ->orderBy('id_categoria', 'desc')
                    ->get();



                $idsClientes = $data
                    ->pluck('id_cliente')
                    ->unique();

                // La primera consulta se busca sobre todo el historial del cliente (sin filtro de fechas),
                // por eso MIN(id_criterio_venta) indica cuál fue la consulta original.
                $primeraConsultaPorCliente = CriterioBusquedaVenta::whereIn(
                    'id_cliente',
                    $idsClientes
                )
                    ->selectRaw(
                        'id_cliente, MIN(id_criterio_venta) as primer_id'
                    )
                    ->groupBy('id_cliente')
                    ->pluck(
                        'primer_id',
                        'id_cliente'
                    );

                $idsDentroDelRango = $data
                    ->pluck('id_criterio_venta')
                    ->flip();

                // Se conservan solo los criterios cuya consulta original está dentro del rango (clientes nuevos).
                $data = $data
                    ->filter(function ($criterio) use (
                        $primeraConsultaPorCliente,
                        $idsDentroDelRango
                    ) {

                        $primerId =
                            $primeraConsultaPorCliente[$criterio->id_cliente] ?? null;

                        return $primerId !== null
                            && $idsDentroDelRango->has($primerId);
                    })
                    ->values();

                // Un cliente puede tener varios criterios: se queda con el más reciente (mayor id).
                $data = $data
                    ->sortByDesc('id_criterio_venta')
                    ->unique('id_cliente')
                    ->sortByDesc('id_categoria')
                    ->values();



                $data = $data
                    ->sortBy('fecha_criterio_venta')
                    ->values();



                $total_criterios = $data->count();



                $conteoAsesores = [];

                $agrupadosPorAsesor = $data->groupBy(function ($criterio) {

                    return $criterio->cliente->asesor->usuario->username
                        ?? 'Sin Asesor';
                });

                foreach ($agrupadosPorAsesor as $username => $criterios) {

                    $conteoAsesores[$username] = $criterios->count();
                }



                $total_tipo_ingreso = [];

                $agrupadosPorIngreso = $data->groupBy(function ($criterio) {

                    return strtolower(
                        $criterio->cliente?->ingreso
                            ?? 'Sin Especificar'
                    );
                });

                foreach ($agrupadosPorIngreso as $ingreso => $criterios) {

                    $total_tipo_ingreso[$ingreso] = $criterios->count();
                }


            // Generales: todos los criterios del rango, un registro por cliente (el más reciente).
            // Los clientes cuya consulta original quedó fuera del rango se marcan con tipo_consulta = 'R'.
            } elseif ($request->consulta === 'Generales') {

                $data = CriterioBusquedaVenta::query()
                    ->with([
                        'tipoInmueble',
                        'zona',
                        'cliente.asesor.usuario',
                        'historialConsultas'
                    ]);


                if (!empty($request->asesor)) {
                    $data->whereHas('cliente', function ($query) use ($request) {
                        $query->where('id_asesor_venta', $request->asesor);
                    });
                }



                $fechaDesde = $request->desde;
                $fechaHasta = $request->hasta;

                if (!empty($fechaDesde) && !empty($fechaHasta)) {

                    $data->whereBetween(
                        'fecha_criterio_venta',
                        [$fechaDesde, $fechaHasta]
                    );
                } elseif (!empty($fechaDesde)) {

                    $data->where(
                        'fecha_criterio_venta',
                        '>=',
                        $fechaDesde
                    );
                } elseif (!empty($fechaHasta)) {

                    $data->where(
                        'fecha_criterio_venta',
                        '<=',
                        $fechaHasta
                    );
                }


                $data = $data
                    ->orderBy('id_categoria', 'desc')
                    ->get();



                $idsClientes = $data
                    ->pluck('id_cliente')
                    ->unique();

                $primeraConsultaPorCliente = CriterioBusquedaVenta::whereIn(
                    'id_cliente',
                    $idsClientes
                )
                    ->selectRaw(
                        'id_cliente, MIN(id_criterio_venta) as primer_id'
                    )
                    ->groupBy('id_cliente')
                    ->pluck(
                        'primer_id',
                        'id_cliente'
                    );



                $idsDentroDelRango = $data
                    ->pluck('id_criterio_venta')
                    ->flip();



                $data = $data
                    ->sortByDesc('id_criterio_venta')
                    ->unique('id_cliente')
                    ->sortByDesc('id_categoria')
                    ->values();



                $data = $data->map(function ($criterio) use (
                    $primeraConsultaPorCliente,
                    $idsDentroDelRango
                ) {

                    $primerId =
                        $primeraConsultaPorCliente[$criterio->id_cliente] ?? null;

                    $criterio->tipo_consulta =
                        ($primerId !== null
                            && !$idsDentroDelRango->has($primerId))
                        ? 'R'
                        : null;

                    return $criterio;
                });



                $data = $data
                    ->sortBy('fecha_criterio_venta')
                    ->values();



                $total_criterios = $data->count();



                $conteoAsesores = [];

                $agrupadosPorAsesor = $data->groupBy(function ($criterio) {

                    return $criterio->cliente->asesor->usuario->username
                        ?? 'Sin Asesor';
                });

                foreach ($agrupadosPorAsesor as $username => $criterios) {

                    $conteoAsesores[$username] = $criterios->count();
                }



                $total_tipo_ingreso = [];

                $agrupadosPorIngreso = $data->groupBy(function ($criterio) {

                    return strtolower(
                        $criterio->cliente?->ingreso
                            ?? 'Sin Especificar'
                    );
                });

                foreach ($agrupadosPorIngreso as $ingreso => $criterios) {

                    $total_tipo_ingreso[$ingreso] = $criterios->count();
                }


            // Reconsultas: igual que Generales, pero solo se listan los clientes que ya consultaban antes del rango ('R').
            } elseif ($request->consulta === 'Reconsultas') {

                $data = CriterioBusquedaVenta::query()
                    ->with([
                        'tipoInmueble',
                        'zona',
                        'cliente.asesor.usuario',
                        'historialConsultas'
                    ]);



                if (!empty($request->asesor)) {
                    $data->whereHas('cliente', function ($query) use ($request) {
                        $query->where('id_asesor_venta', $request->asesor);
                    });
                }



                $fechaDesde = $request->desde;
                $fechaHasta = $request->hasta;

                if (!empty($fechaDesde) && !empty($fechaHasta)) {

                    $data->whereBetween(
                        'fecha_criterio_venta',
                        [$fechaDesde, $fechaHasta]
                    );
                } elseif (!empty($fechaDesde)) {

                    $data->where(
                        'fecha_criterio_venta',
                        '>=',
                        $fechaDesde
                    );
                } elseif (!empty($fechaHasta)) {

                    $data->where(
                        'fecha_criterio_venta',
                        '<=',
                        $fechaHasta
                    );
                }


                $data = $data
                    ->orderBy('id_categoria', 'desc')
                    ->get();



                $idsClientes = $data
                    ->pluck('id_cliente')
                    ->unique();

                $primeraConsultaPorCliente = CriterioBusquedaVenta::whereIn(
                    'id_cliente',
                    $idsClientes
                )
                    ->selectRaw(
                        'id_cliente, MIN(id_criterio_venta) as primer_id'
                    )
                    ->groupBy('id_cliente')
                    ->pluck(
                        'primer_id',
                        'id_cliente'
                    );



                $idsDentroDelRango = $data
                    ->pluck('id_criterio_venta')
                    ->flip();



                $data = $data
                    ->sortByDesc('id_criterio_venta')
                    ->unique('id_cliente')
                    ->sortByDesc('id_categoria')
                    ->values();



                $data = $data->map(function ($criterio) use (
                    $primeraConsultaPorCliente,
                    $idsDentroDelRango
                ) {

                    $primerId =
                        $primeraConsultaPorCliente[$criterio->id_cliente] ?? null;

                    $criterio->tipo_consulta =
                        ($primerId !== null
                            && !$idsDentroDelRango->has($primerId))
                        ? 'R'
                        : null;

                    return $criterio;
                });



                $data = $data
                    ->filter(function ($criterio) {

                        return $criterio->tipo_consulta === 'R';
                    })
                    ->values();



                $data = $data
                    ->sortBy('fecha_criterio_venta')
                    ->values();



                $total_criterios = $data->count();



                $conteoAsesores = [];

                $agrupadosPorAsesor = $data->groupBy(function ($criterio) {

                    return $criterio->cliente->asesor->usuario->username
                        ?? 'Sin Asesor';
                });

                foreach ($agrupadosPorAsesor as $username => $criterios) {

                    $conteoAsesores[$username] = $criterios->count();
                }



                $total_tipo_ingreso = [];

                $agrupadosPorIngreso = $data->groupBy(function ($criterio) {

                    return strtolower(
                        $criterio->cliente?->ingreso
                            ?? 'Sin Especificar'
                    );
                });

                foreach ($agrupadosPorIngreso as $ingreso => $criterios) {

                    $total_tipo_ingreso[$ingreso] = $criterios->count();
                }
            }






            $html = view(
                'pdfs.atcl.listadoPropiedad',
                compact(
                    'data',
                    'username',
                    'pertenece',
                    'sector',
                    'total_criterios',
                    'conteoAsesores',
                    'total_tipo_ingreso',
                    'fechaDesde',
                    'fechaHasta'
                )
            )->render();
        }
        if ($pertenece === 'conversaciones') {
            // Clientes del asesor, su historial de conversaciones y el combinado que se muestra en el PDF.
            $clientes = (new PdfVentaService())->ObtenerClientesAsesor($request->asesor_id);

            // Si se envía id_criterio_venta, el PDF se limita a ese criterio puntual.
            if ($request->filled('id_criterio_venta')) {
                $clientes = $clientes->where('id_criterio_venta', (int) $request->id_criterio_venta)->values();
            }
            $historialConversacion = (new PdfVentaService())->ObtenerHistorialConversacion($clientes);
            $datosTotales = (new PdfVentaService())->CombinarDatos($clientes, $historialConversacion);

            $html = view('pdfs.atcl.listadoPropiedad', compact('datosTotales', 'pertenece', 'sector'))->render();
        }
        if ($pertenece === 'informeNovedades') {
            // Observaciones de tipo 'A' (novedades), filtradas por updated_at dentro del rango si se informó alguno.
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

            // Propiedades modificadas en el mismo rango, incluidas aunque no tengan observaciones.
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
                // Si la propiedad no tiene observaciones, se agrega un registro sintético para que aparezca en el informe.
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

            $html = view('pdfs.atcl.listadoPropiedad', compact('data', 'username', 'pertenece', 'sector', 'fechas'))->render();
        }
        if ($pertenece === 'tiempoOfrecimiento') {
            // Mismos filtros que el listado de propiedades, aplicados por FiltrosPdfService.
            $query = $filtrosService->aplicarFiltrosUnificados($request->all());

            // Carga en una sola consulta las relaciones que usa la vista (evita consultas por cada propiedad).
            $propiedades = $query->with(['observacionesPropiedades', 'tipoInmueble', 'historialEstadosAlquiler', 'propietarios'])->get();

            // El orden por precio y por autorización se aplica sobre la colección ya cargada.
            if ($request->orden === 'precio_asc' || $request->orden === 'precio_desc') {
                $propiedades = $filtrosService->ordenarPorPrecio($propiedades, $request->orden, $sector);
            }

            if ($request->orden === 'autorizacion') {
                $propiedades = $filtrosService->ordenarPorAutorizacion($propiedades, $request->orden, $sector);
            }

            $contadorPropiedades = $propiedades->count();

            // Reúne los IDs de usuario de todas las propiedades para resolver sus nombres con una sola consulta.
            $modifierIds  = $propiedades->pluck('last_modified_by')->filter();
            $asesorIds    = $propiedades->pluck('asesor')->filter();
            $todosLosIds = $modifierIds
                ->merge($asesorIds)
                ->unique()
                ->values();

            // Un único SELECT de usernames indexados por id.
            $usernamesById = $todosLosIds->isNotEmpty()
                ? Usuario::whereIn('id', $todosLosIds)->pluck('username', 'id')->all()
                : [];

            // Reemplaza cada ID por su username; si el ID es nulo o no existe, se muestra '-'.
            foreach ($propiedades as $propiedad) {
                $modId = $propiedad->last_modified_by;
                $propiedad->username = ($modId && isset($usernamesById[$modId]))
                    ? $usernamesById[$modId]
                    : '-';

                $propiedad->captador_int_v = ($propiedad->captador_int_v && isset($usernamesById[$propiedad->captador_int_v]))
                    ? $usernamesById[$propiedad->captador_int_v]
                    : '-';

                $propiedad->captador_int_a = ($propiedad->captador_int_a && isset($usernamesById[$propiedad->captador_int_a]))
                    ? $usernamesById[$propiedad->captador_int_a]
                    : '-';

                $propiedad->asesor = ($propiedad->asesor && isset($usernamesById[$propiedad->asesor]))
                    ? $usernamesById[$propiedad->asesor]
                    : '-';

                $historial = $propiedad->fecha_ofrecimiento;
                $propiedad->fecha_antiguedad = Carbon::parse($historial);
                $propiedad->antiguedad = $this->formatearAntiguedad($historial);

                if ($historial && !empty($historial->fecha_alquiler)) {
                    $propiedad->fecha_antiguedad = Carbon::parse($historial->fecha_alquiler);
                    $propiedad->antiguedad = $this->formatearAntiguedad($historial->fecha_alquiler);
                } else {
                    $propiedad->fecha_antiguedad = Carbon::parse($propiedad->created_at);
                    $propiedad->antiguedad = $this->formatearAntiguedad($propiedad->created_at);
                }
            }

            // Agrupa por tipo de inmueble (grupos en orden alfabético) y dentro de cada grupo ordena por fecha_antiguedad.
            $propiedades = $propiedades
                ->groupBy(function ($propiedad) {
                    return $propiedad->tipoInmueble->inmueble ?? 'Sin tipo';
                })
                ->sortKeys()
                ->map(function ($grupo) {
                    // Orden ascendente de fecha: la propiedad con fecha más antigua queda primero.
                    return $grupo->sortBy('fecha_antiguedad')->values();
                })
                ->flatten(1)
                ->values();

            $contadorPropiedades = $propiedades->count();
            $conteoPorTipo = $propiedades
                ->groupBy(function ($propiedad) {
                    // El nombre de la relación en el modelo Propiedad es tipoInmueble (en camelCase)
                    return $propiedad->tipoInmueble->inmueble ?? 'SIN TIPO';
                })
                ->map(function ($grupo) {
                    return $grupo->count();
                });

            // Cantidad por tipo de inmueble, p. ej. ['DEPARTAMENTO' => 5, 'CASA' => 3].
            $conteoPorTipoArray = $conteoPorTipo->toArray();

            $html = view('pdfs.atcl.listadoPropiedad', compact(
                'propiedades',
                'username',
                'pertenece',
                'sector',
                'contadorPropiedades',
                'conteoPorTipoArray'
            ))->render();
        }

        if ($pertenece === 'listadoTasaciones') {
            $query = Tasaciones::with([
                'tipoInmueble',
                'calleRelacion',
                'propietarioRelacion',
                'localidad',
                'tasadorSector.usuario',
            ]);

            if ($request->filled('calle')) {
                $query->where('calle', $request->calle);
            } elseif ($request->filled('calle_busqueda')) {
                $calleBusqueda = $request->calle_busqueda;
                $query->whereHas('calleRelacion', function ($q) use ($calleBusqueda) {
                    $q->where('name', 'like', '%' . $calleBusqueda . '%');
                });
            }

            if ($request->filled('propietario')) {
                $query->where('propietario', $request->propietario);
            } elseif ($request->filled('propietario_busqueda')) {
                $propietarioBusqueda = $request->propietario_busqueda;
                $query->whereHas('propietarioRelacion', function ($q) use ($propietarioBusqueda) {
                    $q->where('nombre', 'like', '%' . $propietarioBusqueda . '%')
                        ->orWhere('apellido', 'like', '%' . $propietarioBusqueda . '%')
                        ->orWhere('documento', 'like', '%' . $propietarioBusqueda . '%');
                });
            }

            if ($request->filled('tipo_inmueble')) {
                $query->where('tipo_inmueble', $request->tipo_inmueble);
            }

            if ($request->filled('tipo_tasacion')) {
                $query->where('tipo_tasacion', $request->tipo_tasacion);
            }

            if ($request->filled('tasador')) {
                $query->where('tasador', $request->tasador);
            }

            if ($request->filled('tasacion_informada_prop')) {
                $query->where('tasacion_informada_prop', $request->tasacion_informada_prop);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha', '<=', $request->fecha_hasta);
            }

            $tasaciones = $query->orderByDesc('fecha')->orderByDesc('id')->get();
            $contadorTasaciones = $tasaciones->count();

            $usuario_id = auth('api')->id();
            $authUser = $usuario_id ? Usuario::find($usuario_id) : null;
            $username = $authUser->username ?? '-';

            $html = view('pdfs.atcl.listadoPropiedad', compact(
                'tasaciones',
                'username',
                'pertenece',
                'sector',
                'contadorTasaciones'
            ))->render();
        }

        // Los listados de ofrecimiento, conversaciones e informe de novedades salen en vertical; el resto en horizontal.
        $orientacion = 'landscape';
        if ($pertenece === 'ofrecimientoVenta' || $pertenece === 'conversaciones' || $pertenece === 'informeNovedades') {
            $orientacion = 'portrait';
        }
        // La rama de consultas ingresadas no asigna $username, así que el pie del PDF
        // muestra el usuario autenticado, obtenido aquí antes de generar el archivo.
        if ($pertenece === 'consultasIngresadas') {
            $usuario_id = auth('api')->id();
            $authUser = $usuario_id ? Usuario::find($usuario_id) : null;
            $username = $authUser->username ?? '-';
        }




        return response()->streamDownload(function () use ($html, $username, $orientacion) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('legal')
                ->margins(10, 1, 10, 1)
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', true)
                ->setOption('headerTemplate', '
                <div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;">
                <span style="text-align:left;">Ficha de Propiedad</span><span style="text-align:right;">Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
                ->$orientacion()
                ->setOption('footerTemplate', '
                <div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;">
                <span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:center;">' . $username . '</span>  <span style="text-align:right;" class="date"></span></div>')
                ->pdf();
        }, 'ficha_propiedad.pdf');
    }

    /**
     * Devuelve como JSON los criterios de búsqueda activos de venta que cumplen los filtros.
     *
     * Solo se consideran criterios con estado_criterio_venta = 'Activo'. El filtro por asesor
     * usa el cliente del criterio; los demás filtros son opcionales y se aplican solo si llegan con valor.
     *
     * @param  \Illuminate\Http\Request  $request  Puede incluir asesor_id, zona_id, tipo, cantidad_dormitorios,
     *                                             estado (id_categoria), precio_minimo y precio_maximo.
     * @return \Illuminate\Http\JsonResponse  Objeto con "total" y "data" (una fila por criterio).
     */
    public function criteriosActivosListado(Request $request)
    {
        $query = CriterioBusquedaVenta::where('estado_criterio_venta', 'Activo')
            ->with(['tipoInmueble', 'zona', 'cliente']);

        // Sin asesor seleccionado ("Todos") no se restringe por cliente.
        if (!empty($request->asesor_id)) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('id_asesor_venta', $request->asesor_id);
            });
        }

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

        // El rango de precio se compara contra precio_hasta del criterio; cada límite es opcional.
        $precioMin = $request->input('precio_minimo');
        $precioMax = $request->input('precio_maximo');

        if ($precioMin && $precioMax) {
            $query->whereBetween('precio_hasta', [$precioMin, $precioMax]);
        } elseif ($precioMin) {
            $query->where('precio_hasta', '>=', $precioMin);
        } elseif ($precioMax) {
            $query->where('precio_hasta', '<=', $precioMax);
        }

        $criterios = $query->orderBy('id_categoria', 'desc')->get();

        $filas = $criterios->map(function ($criterio) {
            return [
                'id_criterio_venta' => $criterio->id_criterio_venta,
                'asesor_id' => $criterio->cliente->id_asesor_venta ?? null,
                'cliente' => $criterio->cliente->nombre ?? '-',
                'telefono' => $criterio->cliente->telefono ?? '-',
                'tipo_inmueble' => $criterio->tipoInmueble->inmueble ?? '-',
                'categoria' => $criterio->id_categoria ?? '-',
                'zona' => $criterio->zona->name ?? '-',
                'cant_dormitorios' => $criterio->cant_dormitorios ?? '-',
                'cochera' => $criterio->cochera ?? '-',
                'precio' => $criterio->precio_hasta ?? '-',
            ];
        })->values();

        return response()->json([
            'total' => $filas->count(),
            'data' => $filas,
        ]);
    }

    /**
     * Convierte la distancia entre una fecha y hoy en texto, por ejemplo "2 años 3 meses 5 días".
     *
     * Se usan solo las unidades mayores a cero. Si la diferencia es menor a un día se devuelve "0 días".
     *
     * @param  mixed  $fecha  Valor que Carbon::parse acepta (string, DateTime o Carbon).
     * @return string
     */
    private function formatearAntiguedad($fecha): string
    {
        $diff = Carbon::parse($fecha)->diff(now());

        $partes = [];

        if ($diff->y > 0) {
            $partes[] = $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
        }

        if ($diff->m > 0) {
            $partes[] = $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
        }

        if ($diff->d > 0) {
            $partes[] = $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
        }

        return implode(' ', $partes) ?: '0 días';
    }

    /**
     * Devuelve en JSON los datos de conversación de un criterio puntual.
     * Usado por el modal del front en lugar del PDF.
     */
    public function conversacionesJson(Request $request)
    {
        $clientes = (new PdfVentaService())->ObtenerClientesAsesor($request->asesor_id);

        if ($request->filled('id_criterio_venta')) {
            $clientes = $clientes->where('id_criterio_venta', (int) $request->id_criterio_venta)->values();
        }

        $historialConversacion = (new PdfVentaService())->ObtenerHistorialConversacion($clientes);
        $datosTotales = (new PdfVentaService())->CombinarDatos($clientes, $historialConversacion);

        return response()->json($datosTotales);
    }
}
