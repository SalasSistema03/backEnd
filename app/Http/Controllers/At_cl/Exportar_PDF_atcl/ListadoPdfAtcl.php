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
use Illuminate\Support\Carbon;

class ListadoPdfAtcl
{
    /**
     * Listado de propiedades unificado para Venta y Alquiler
     */
    public function listadoPropiedad(Request $request)
    {
        $informacionMostrar = $request->informacionMostrar;
        //Log::info('request', [$request->all()]);
        $pertenece = $request->pertenece;
        $username = '-';
        $sector = $request->sector;
        $contadorPropiedades = 0;
        $filtrosService = new FiltrosPdfService;

        if ($pertenece === 'listadoPropiedades') {



            // Usar el filtro unificado (el ordenamiento se aplica dentro, excepto precio)
            $query = $filtrosService->aplicarFiltrosUnificados($request->all());

            // Ejecutar la query trayendo también la relación de observaciones y tipoInmueble
            $propiedades = $query->with(['observacionesPropiedades', 'tipoInmueble', 'historialEstadosAlquiler'])->get();

            // Solo ordenar por precio si es necesario (post-query)
            if ($request->orden === 'precio_asc' || $request->orden === 'precio_desc') {
                $propiedades = $filtrosService->ordenarPorPrecio($propiedades, $request->orden, $sector);
            }

            if ($request->orden === 'autorizacion') {
                //Log::info('entro autorizacion', [$request->orden]);
                $propiedades = $filtrosService->ordenarPorAutorizacion($propiedades, $request->orden, $sector);
            }

            $contadorPropiedades = $propiedades->count();

            // --- Recolectar TODOS los IDs de usuario que necesitamos en una sola pasada ---
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

            // Una sola query para traer todos los usernames
            $usernamesById = $todosLosIds->isNotEmpty()
                ? Usuario::whereIn('id', $todosLosIds)->pluck('username', 'id')->all()
                : [];

            // Una sola pasada por la colección, aplicando todas las transformaciones
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


                /* if ($request->estado_id === 1 || $request->estado_id === 2) {


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
                } */
            }

            // Usuario actual
            $usuario_id = auth('api')->id();
            $authUser = $usuario_id ? Usuario::find($usuario_id) : null;
            $username = $authUser->username ?? '-';

            // Log::info($propiedades);
            $conteoPorTipo = $propiedades
                ->groupBy(function ($propiedad) {
                    // El nombre de la relación en el modelo Propiedad es tipoInmueble (en camelCase)
                    return $propiedad->tipoInmueble->inmueble ?? 'SIN TIPO';
                })
                ->map(function ($grupo) {
                    return $grupo->count();
                });

            // Si querés un array plano simple: ['DEPARTAMENTO' => 5, 'CASA' => 3, ...]
            $conteoPorTipoArray = $conteoPorTipo->toArray();

            //Log::info($conteoPorTipoArray);

            // Generar HTML
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
            /*  $contadorPropietarios = 0; */
            //Log::info('entro a propietarios');
            $propietario = $request->propietario;
            //sLog::info('propietario', [$propietario]);
            $campoCodigo = ($sector === 'Alquiler') ? 'cod_alquiler' : 'cod_venta';
            Log::info($campoCodigo);

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
                //Log::info('request', [$request->all()]);

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

            // Aplicar ordenamiento
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
                // Por defecto o si orden === 'calle'
                $propiedades = $propiedades->sortBy(function ($propiedad) {
                    return $propiedad->calle->name ?? '';
                });
            }

            foreach ($propiedades as $propiedad) {
                $contadorPropiedades++;
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

            if ($request->consulta === 'Consultas Nuevas') {
                $data = CriterioBusquedaVenta::query()
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

                // 1. Sacamos los id_cliente que aparecen en el resultado ya filtrado por fecha
                $idsClientes = $data->pluck('id_cliente')->unique();

                // 2. Buscamos, para esos clientes, cual es su PRIMER id_criterio_venta en TODA la tabla
                //    (sin importar fecha ni estado_criterio_venta)
                $primeraConsultaPorCliente = CriterioBusquedaVenta::whereIn('id_cliente', $idsClientes)
                    ->selectRaw('id_cliente, MIN(id_criterio_venta) as primer_id')
                    ->groupBy('id_cliente')
                    ->pluck('primer_id', 'id_cliente');

                // 3. Set de ids que quedaron dentro del rango filtrado (para chequear pertenencia rapido)
                $idsDentroDelRango = $data->pluck('id_criterio_venta')->flip();

                // 4. Si la primera consulta GLOBAL del cliente no esta dentro del rango filtrado,
                //    significa que lo que aparece aca es una reconsulta de algo anterior -> se excluye por completo
                $data = $data->filter(function ($criterio) use ($primeraConsultaPorCliente, $idsDentroDelRango) {
                    $primerId = $primeraConsultaPorCliente[$criterio->id_cliente] ?? null;

                    return $primerId !== null && $idsDentroDelRango->has($primerId);
                })->values();

                // 5. De los que quedaron (primera consulta real dentro del rango), si el cliente
                //    reconsulto mas de una vez DENTRO del mismo rango, nos quedamos con el id_criterio_venta mas grande
                $data = $data->sortByDesc('id_criterio_venta')
                    ->unique('id_cliente')
                    ->sortByDesc('id_categoria')
                    ->values();

                //por ultimo las ordenamos por fecha de menor a mayor
                $data = $data->sortBy('fecha_criterio_venta')->values();

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
            } elseif ($request->consulta === 'Generales') {

                $data = CriterioBusquedaVenta::query()
                    //->where('estado_criterio_venta', 'Activo')
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

                // 1. Sacamos los id_cliente que aparecen en el resultado ya filtrado por fecha
                $idsClientes = $data->pluck('id_cliente')->unique();

                // 2. Buscamos, para esos clientes, cual es su PRIMER id_criterio_venta en TODA la tabla
                //    (sin importar fecha ni estado_criterio_venta)
                $primeraConsultaPorCliente = CriterioBusquedaVenta::whereIn('id_cliente', $idsClientes)
                    ->selectRaw('id_cliente, MIN(id_criterio_venta) as primer_id')
                    ->groupBy('id_cliente')
                    ->pluck('primer_id', 'id_cliente');

                // 3. Set de ids que quedaron dentro del rango filtrado (para chequear pertenencia rapido)
                $idsDentroDelRango = $data->pluck('id_criterio_venta')->flip();

                // 4. Si el cliente reconsulto mas de una vez DENTRO del mismo rango, nos quedamos
                //    con el id_criterio_venta mas grande (la mas reciente)
                $data = $data->sortByDesc('id_criterio_venta')
                    ->unique('id_cliente')
                    ->sortByDesc('id_categoria')
                    ->values();

                // 5. Marcamos cada registro como "R" (reconsulta) si su primera consulta GLOBAL
                //    del cliente no esta dentro del rango filtrado; sino queda null (consulta nueva del periodo)
                $data = $data->map(function ($criterio) use ($primeraConsultaPorCliente, $idsDentroDelRango) {
                    $primerId = $primeraConsultaPorCliente[$criterio->id_cliente] ?? null;

                    $criterio->tipo_consulta = ($primerId !== null && !$idsDentroDelRango->has($primerId))
                        ? 'R'
                        : null;

                    return $criterio;
                });

                //por ultimo las ordenamos por fecha de menor a mayor
                $data = $data->sortBy('fecha_criterio_venta')->values();

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
                // Log::info($data);

                // 3. Agrupamos y contamos cuántos criterios tiene cada tipo de ingreso (Whatsapp, Sitio web, etc)
                $total_tipo_ingreso = [];
                /* no distinguir entre mayuculas y minisculas */
                $agrupadosPorIngreso = $data->groupBy(function ($criterio) {
                    return strtolower($criterio->cliente?->ingreso ?? 'Sin Especificar');
                });

                foreach ($agrupadosPorIngreso as $ingreso => $criterios) {
                    $total_tipo_ingreso[$ingreso] = $criterios->count();
                }
            } elseif ($request->consulta === 'Reconsultas') {

                $data = CriterioBusquedaVenta::query()
                    //->where('estado_criterio_venta', 'Activo')
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

                // 1. Sacamos los id_cliente que aparecen en el resultado ya filtrado por fecha
                $idsClientes = $data->pluck('id_cliente')->unique();

                // 2. Buscamos, para esos clientes, cual es su PRIMER id_criterio_venta en TODA la tabla
                //    (sin importar fecha ni estado_criterio_venta)
                $primeraConsultaPorCliente = CriterioBusquedaVenta::whereIn('id_cliente', $idsClientes)
                    ->selectRaw('id_cliente, MIN(id_criterio_venta) as primer_id')
                    ->groupBy('id_cliente')
                    ->pluck('primer_id', 'id_cliente');

                // 3. Set de ids que quedaron dentro del rango filtrado (para chequear pertenencia rapido)
                $idsDentroDelRango = $data->pluck('id_criterio_venta')->flip();

                // 4. Si el cliente reconsulto mas de una vez DENTRO del mismo rango, nos quedamos
                //    con el id_criterio_venta mas grande (la mas reciente)
                $data = $data->sortByDesc('id_criterio_venta')
                    ->unique('id_cliente')
                    ->sortByDesc('id_categoria')
                    ->values();

                // 5. Marcamos cada registro como "R" (reconsulta) si su primera consulta GLOBAL
                //    del cliente no esta dentro del rango filtrado; sino queda null (consulta nueva del periodo)
                $data = $data->map(function ($criterio) use ($primeraConsultaPorCliente, $idsDentroDelRango) {
                    $primerId = $primeraConsultaPorCliente[$criterio->id_cliente] ?? null;

                    $criterio->tipo_consulta = ($primerId !== null && !$idsDentroDelRango->has($primerId))
                        ? 'R'
                        : null;

                    return $criterio;
                });

                // 6. Nos quedamos SOLO con las reconsultas (descartamos las consultas nuevas del periodo)
                $data = $data->filter(function ($criterio) {
                    return $criterio->tipo_consulta === 'R';
                })->values();

                //por ultimo las ordenamos por fecha de menor a mayor
                $data = $data->sortBy('fecha_criterio_venta')->values();

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

                // 3. Agrupamos y contamos cuántos criterios tiene cada tipo de ingreso (Whatsapp, Sitio web, etc)
                $total_tipo_ingreso = [];
                /* no distinguir entre mayuculas y minisculas */
                $agrupadosPorIngreso = $data->groupBy(function ($criterio) {
                    return strtolower($criterio->cliente?->ingreso ?? 'Sin Especificar');
                });

                foreach ($agrupadosPorIngreso as $ingreso => $criterios) {
                    $total_tipo_ingreso[$ingreso] = $criterios->count();
                }
            }

            $html = view('pdfs.atcl.listadoPropiedad', compact('data', 'username', 'pertenece', 'sector', 'total_criterios', 'conteoAsesores', 'total_tipo_ingreso', 'fechaDesde', 'fechaHasta'))->render();
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
        if ($pertenece === 'tiempoOfrecimiento') {
            // Usar el filtro unificado (el ordenamiento se aplica dentro, excepto precio)
            $query = $filtrosService->aplicarFiltrosUnificados($request->all());

            // Ejecutar la query trayendo también la relación de observaciones y tipoInmueble
            $propiedades = $query->with(['observacionesPropiedades', 'tipoInmueble', 'historialEstadosAlquiler'])->get();

            // Solo ordenar por precio si es necesario (post-query)
            if ($request->orden === 'precio_asc' || $request->orden === 'precio_desc') {
                $propiedades = $filtrosService->ordenarPorPrecio($propiedades, $request->orden, $sector);
            }

            if ($request->orden === 'autorizacion') {
                //Log::info('entro autorizacion', [$request->orden]);
                $propiedades = $filtrosService->ordenarPorAutorizacion($propiedades, $request->orden, $sector);
            }

            $contadorPropiedades = $propiedades->count();

            // --- Recolectar TODOS los IDs de usuario que necesitamos en una sola pasada ---
            $modifierIds  = $propiedades->pluck('last_modified_by')->filter();
            $asesorIds    = $propiedades->pluck('asesor')->filter();
            $todosLosIds = $modifierIds
                ->merge($asesorIds)
                ->unique()
                ->values();

            // Una sola query para traer todos los usernames
            $usernamesById = $todosLosIds->isNotEmpty()
                ? Usuario::whereIn('id', $todosLosIds)->pluck('username', 'id')->all()
                : [];

            // Una sola pasada por la colección, aplicando todas las transformaciones
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

                $historial = $propiedad->historialEstadosAlquiler;

                if ($historial && in_array($historial->id_estado_alquiler, [1, 2])) {
                    $propiedad->fecha_antiguedad = Carbon::parse($historial->fecha_alquiler);
                    $propiedad->antiguedad = $this->formatearAntiguedad($historial->fecha_alquiler);
                } else {
                    //$propiedad->fecha_antiguedad = Carbon::parse($propiedad->created_at);
                    //$propiedad->antiguedad = $this->formatearAntiguedad($propiedad->created_at);
                }
            }

            // --- NUEVO: agrupar por tipo de inmueble y ordenar cada grupo por antigüedad ---
            $propiedades = $propiedades
                ->groupBy(function ($propiedad) {
                    return $propiedad->tipoInmueble->inmueble ?? 'Sin tipo';
                })
                ->sortKeys() // opcional: ordena los grupos alfabéticamente (CASA, DEPARTAMENTO, etc.)
                ->map(function ($grupo) {
                    // Mayor antigüedad primero = fecha más vieja primero = orden ascendente
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

            // Si querés un array plano simple: ['DEPARTAMENTO' => 5, 'CASA' => 3, ...]
            $conteoPorTipoArray = $conteoPorTipo->toArray();

            //Log::info('propiedades', [$propiedades]);
            $html = view('pdfs.atcl.listadoPropiedad', compact(
                'propiedades',
                'username',
                'pertenece',
                'sector',
                'contadorPropiedades',
                'conteoPorTipoArray'
            ))->render();
        }

        $orientacion = 'landscape';
        if ($pertenece === 'ofrecimientoVenta' || $pertenece === 'conversaciones' || $pertenece === 'informeNovedades') {
            $orientacion = 'portrait';
        }
        //Se coloca esta condicion porque sino username del final se sobreescribe con otro nombre de usuario
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
}
