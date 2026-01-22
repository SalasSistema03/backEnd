<?php

namespace App\Http\Controllers\At_cl\Exportar_PDF_atcl;

use App\Models\At_cl\Propiedades_padron;
use Illuminate\Http\Request;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Zona;
use App\Models\At_cl\Calle;
use App\Models\At_cl\Estado_venta;
use Illuminate\Support\Facades\DB;
use App\Models\At_cl\Padron;
use App\Models\At_cl\Usuario;
use App\Services\At_cl\documentacionService;
use App\Services\At_cl\FotosService;
use App\Services\At_cl\HistorialFechasService;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\At_cl\PrecioService;
use App\Services\At_cl\PropiedadService;
use App\Services\At_cl\ObservacionesPropiedadesService;
use App\Services\At_cl\TasacionService;
use App\Services\At_cl\Propiedades_padronService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use App\Services\At_cl\FiltrosPdfService;
use App\Models\cliente\CriterioBusquedaVenta;
use App\Models\cliente\HistorialCodOfrecimiento;
use App\Models\cliente\HistorialCodMuestra;
use App\Models\cliente\HistorialCodigoConsulta;
use App\Models\cliente\Clientes;
use App\Services\At_cl\Exportar_PDF_atcl\PdfVentaService;

class Pdf_venta
{
    protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
        $estado_venta, $localidad, $barrio, $Propiedades, $contrato_cabecera, $observaciones_propiedades, $provincia,
        $padron, $precio,  $usuario_id, $accessService, $propiedadService, $precioService, $observacionesPropiedadesService,
        $usuario, $fotoService, $documentacionService, $historialFechasService, $tasacionService, $propiedad_padronService,
        $filtroService, $pdfVentaService;


    public function __construct(
        PropiedadService $propiedadService,
        PrecioService $precioService,
        ObservacionesPropiedadesService $observacionesService,
        FotosService $fotoService,
        documentacionService $documentacionService,
        HistorialFechasService $historialFechasService,
        TasacionService $tasacionService,
        Propiedades_padronService $propiedad_padronService,
        FiltrosPdfService $filtroService,
        PdfVentaService $pdfVentaService
    ) {
        $this->propiedadService = $propiedadService;
        $this->precioService = $precioService;
        $this->observacionesPropiedadesService = $observacionesService;
        $this->fotoService = $fotoService;
        $this->documentacionService = $documentacionService;
        $this->historialFechasService = $historialFechasService;
        $this->tasacionService = $tasacionService;
        $this->propiedad_padronService = $propiedad_padronService;

        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
        $this->historialFechasService = $historialFechasService;
        $this->tasacionService = $tasacionService;
        $this->propiedad_padronService = $propiedad_padronService;
        $this->filtroService = $filtroService;
        $this->pdfVentaService = $pdfVentaService;
    }
    /*----------------------------------- VENTA -----------------------------------*/

    public function ViewPropiedadesVenta()
    {
        // Definimos un array con los nombres de los botones
        $btnNombres = [
            'listarPropiedadesVenta',
            'listarPropietarioVenta',
            'listarPropiedadesAsesorVenta',
            'listarOfrecimientoVenta',
            'listarDevolucionesVenta',
            'listarConversacionesVenta'
        ];
        // Inicializamos un array vacío para almacenar los accesos
        $accesos = [];
        // Recorremos cada nombre de botón
        foreach ($btnNombres as $btnNombre) {
            // Verificamos si el usuario tiene acceso a cada botón y almacenamos el resultado en el array de accesos
            $accesos[$btnNombre] = $this->accessService->tieneAcceso($btnNombre);
        }
        // Asignamos el acceso a 'propietario' a una variable
        $tieneAccesoPropietario = $accesos['listarPropietarioVenta'];
        // Asignamos el acceso a 'InformacionVenta' a una variable
        $tieneAccesoInformacionVenta = $accesos['listarPropiedadesVenta'];
        // Asignamos el acceso a 'InformacionVenta' a una variable
        $tieneAccesoInformacionVentaPropiedadAsesor = $accesos['listarPropiedadesAsesorVenta'];
        // Asignamos el acceso a 'InformacionVenta' a una variable
        $tieneAccesoInformacionVentaOfrecimiento = $accesos['listarOfrecimientoVenta'];
        // Asignamos el acceso a 'InformacionVenta' a una variable
        $tieneAccesoInformacionVentaDevoluciones = $accesos['listarDevolucionesVenta'];
        // Asignamos el acceso a 'conversacionVenta' a una variable
        $tieneAccesoInformacionVentaConversacion = $accesos['listarConversacionesVenta'];

        $estados = Estado_venta::all();
        $tipos = Tipo_inmueble::all();
        $zonas = Zona::all();
        $calle = Calle::all();
        // Desde la conexión de usuario_sector
        $usuarioSector = DB::connection('mysql5')->table('usuario_sector')->get();


        // Desde la conexión de usuarios
        $usuarios = DB::connection('mysql4')->table('usuarios')->get();

        // Devolvemos los usuarios que son asesores
        $asesores = $usuarioSector->map(function ($us) use ($usuarios) {
            $usuario = $usuarios->firstWhere('id', $us->id_usuario);
            return [
                'id_usuario' => $us->id_usuario,
                'name' => $usuario ? $usuario->name : null,
                'username' => $usuario ? $usuario->username : null,
            ];
        });

        // Devolvemos los propietarios que tienen propiedades en venta
        $propietarios = Padron::whereHas('propiedad', function ($query) {
            $query->whereNotNull('cod_venta');
        })->get();

        return view('atencionAlCliente.propiedad.pdf.0vistasListadosVenta', compact(
            'estados',
            'tipos',
            'zonas',
            'calle',
            'propietarios',
            'tieneAccesoPropietario',
            'tieneAccesoInformacionVenta',
            'asesores',
            'tieneAccesoInformacionVentaPropiedadAsesor',
            'tieneAccesoInformacionVentaOfrecimiento',
            'tieneAccesoInformacionVentaDevoluciones',
            'tieneAccesoInformacionVentaConversacion'
        ));
    }


    public function generarPDFlistadoEstadosVenta(Request $request)
    {

        $usuario = $this->usuario;

        $pertenece = $request->input('pertenece');

        if ($pertenece == 'estadosVentaGeneral') {
            if ($request->input('orden') == 'precio_asc') {
                $propiedades = $this->filtroService->aplicarFiltrosV($request->all())
                    ->whereNotNull('cod_venta')
                    ->get()
                    ->sortBy(function ($propiedad) {
                        return $propiedad->precio->moneda_venta_dolar ?? $propiedad->precio->moneda_venta_pesos ?? 0;
                    })
                    ->values(); // reindexa la colección
            } elseif ($request->input('orden') == 'precio_desc') {
                $propiedades = $this->filtroService->aplicarFiltrosV($request->all())
                    ->whereNotNull('cod_venta')
                    ->get()
                    ->sortByDesc(function ($propiedad) {
                        return $propiedad->precio->moneda_venta_dolar ?? $propiedad->precio->moneda_venta_pesos ?? 0;
                    })
                    ->values(); // reindexa la colección
            } else {
                $propiedades = $this->filtroService->aplicarFiltrosV($request->all())
                    ->whereNotNull('cod_venta')
                    ->get();
            }



            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('propiedades', 'pertenece')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'landscape') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 15) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 3) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'estadoPropietarioV') {
            $propietarioId = $request->input('propietarioo');
            $propiedades = $this->pdfVentaService->ObtenerPropiedadesPropietarios($propietarioId);



            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('propiedades', 'pertenece')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'landscape') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 15) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 3) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'criterios-activos') {

            $query = CriterioBusquedaVenta::where('usuario_id', $request->input('asesor'))
                ->where('estado_criterio_venta', 'Activo')
                ->with(['tipoInmueble', 'zona', 'cliente']);





            if ($request->has('zona') && $request->input('zona') != null) {
                $query->where('id_zona', $request->input('zona'));
            }

            if ($request->has('tipo_inmueble') && $request->input('tipo_inmueble') != null) {
                $query->where('id_tipo_inmueble', $request->input('tipo_inmueble'));
            }

            if ($request->has('dormitorios') && $request->input('dormitorios') != null) {
                $query->where('cant_dormitorios', $request->input('dormitorios'));
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


            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('criterios_vendedor', 'pertenece')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'landscape') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 15) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 3) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'ofrecimiento') {
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
                        (SELECT COUNT(*) FROM sistema_clientes.historial_cod_consulta 
                          WHERE codigo_consulta = p.cod_venta $filtroFechaConsulta) as total_consultas,
                        (SELECT COUNT(*) FROM sistema_clientes.historial_cod_muestra 
                          WHERE codigo_muestra = p.cod_venta $filtroFechaConsulta) as total_muestras,
                        (SELECT COUNT(*) FROM sistema_clientes.historial_cod_ofrecimiento 
                          WHERE codigo_ofrecimiento = p.cod_venta $filtroFechaConsulta) as total_ofrecimientos,
                        c.name as calle
                    FROM propiedades p
                    INNER JOIN calle c ON p.id_calle = c.id
                    WHERE p.cod_venta IS NOT NULL";

            $query = DB::connection('mysql')->select($sql, $parametros);



            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('query', 'pertenece', 'fechaDesde', 'fechaHasta')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'portrait') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 15) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('footer-right', date('d-m-Y')) // Texto pie derecho
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'devoluciones') {
            $codigo = $request->input('codigo');
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


            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('datosTotales', 'pertenece', 'codigo')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'landscape') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 15) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('footer-right', date('d-m-Y')) // Texto pie derecho
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'conversaciones') {

            $clientes = $this->pdfVentaService->ObtenerClientesAsesor($request->asesor);
            $historialConversacion = $this->pdfVentaService->ObtenerHistorialConversacion($clientes);
            $datosTotales = $this->pdfVentaService->CombinarDatos($clientes, $historialConversacion);


            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('datosTotales', 'pertenece')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'portrait') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 15) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('footer-right', date('d-m-Y')) // Texto pie derecho
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'listado-propiedades') {

           if ($request->orden == 'precio_asc') {
                $propiedades = $this->filtroService->aplicarFiltrosV($request->all())
                    ->whereNotNull('cod_venta')
                    ->get()
                    ->sortBy(function ($propiedad) {
                        return $propiedad->precio->moneda_venta_dolar ?? $propiedad->precio->moneda_venta_pesos ?? 0;
                    })
                    ->values();
            }elseif($request->input('orden') == 'precio_desc'){
                 $propiedades = $this->filtroService->aplicarFiltrosV($request->all())
                    ->whereNotNull('cod_venta')
                    ->get()
                    ->sortByDesc(function ($propiedad) {
                        return $propiedad->precio->moneda_venta_dolar ?? $propiedad->precio->moneda_venta_pesos ?? 0;
                    })
                    ->values();
            }else{
            $propiedades = $this->filtroService->aplicarFiltrosV($request->all())
                ->whereNotNull('cod_venta')
                ->get();
            }
            $propiedades = $this->filtroService->traerEstadoVenta($propiedades);
            $propiedades = $this->filtroService->traerPropietarios($propiedades);
            $propiedades = $this->filtroService->camposSeleccionados($propiedades, $request->input('campos_seleccionados'));
            $propiedades = $this->filtroService->traerFolio($propiedades);



            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('propiedades', 'pertenece')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'landscape') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 5) // Margen superior (mm)
                ->setOption('margin-bottom', 5) // Margen inferior (mm)
                ->setOption('margin-left', 8) // Margen izquierdo (mm)
                ->setOption('margin-right', 8) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', $usuario->username) // Texto pie izquierdo
                ->setOption('footer-font-size', 6) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]') // pagina x de y
                ->setOption('footer-right', date('d-m-Y')) // Texto pie derecho
                ->setOption('zoom', 0.85);

            return $pdf->stream("listadoEstados.pdf");
        }
    }
}
