<?php

namespace App\Http\Controllers\At_cl\Exportar_PDF_atcl;

use App\Models\At_cl\Propiedades_padron;
use Illuminate\Http\Request;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Zona;
use App\Models\At_cl\Calle;
use App\Models\At_cl\Estado_alquiler;
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

class Pdf_alquiler
{
    protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
        $estado_venta, $localidad, $barrio, $Propiedades, $contrato_cabecera, $observaciones_propiedades, $provincia,
        $padron, $precio,  $usuario_id, $accessService, $propiedadService, $precioService, $observacionesPropiedadesService,
        $usuario, $fotoService, $documentacionService, $historialFechasService, $tasacionService, $propiedad_padronService, $filtroService;


    public function __construct(
        PropiedadService $propiedadService,
        PrecioService $precioService,
        ObservacionesPropiedadesService $observacionesService,
        FotosService $fotoService,
        documentacionService $documentacionService,
        HistorialFechasService $historialFechasService,
        TasacionService $tasacionService,
        Propiedades_padronService $propiedad_padronService,
        FiltrosPdfService $filtroService
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
    }


    /*----------------------------------- ALQUILER -----------------------------------*/

    /* Vista para seleccionar el estado Alquiler*/
    public function ViewPropiedadesAlquiler()
    {
        // Definimos un array con los nombres de los botones
        $btnNombres = [
            'listarPropiedadesAlquiler',
            'listarPropietarioAlquiler'
        ];
        // Inicializamos un array vacío para almacenar los accesos
        $accesos = [];
        // Recorremos cada nombre de botón
        foreach ($btnNombres as $btnNombre) {
            // Verificamos si el usuario tiene acceso a cada botón y almacenamos el resultado en el array de accesos
            $accesos[$btnNombre] = $this->accessService->tieneAcceso($btnNombre);
        }
        // Asignamos el acceso a 'propietario' a una variable
        $tieneAccesoPropietario = $accesos['listarPropietarioAlquiler'];
        // Asignamos el acceso a 'InformacionAlquiler' a una variable
        $tieneAccesoInformacionAlquiler = $accesos['listarPropiedadesAlquiler'];

        $estados = Estado_alquiler::all();
        $tipos = Tipo_inmueble::all();
        $zonas = Zona::all();
        $calle = Calle::all();
        $propietarios = Padron::whereHas('propiedad', function ($query) {
            $query->whereNotNull('cod_alquiler');
        })->get();
        return view('atencionAlCliente.propiedad.pdf.0vistasListadosAlquiler', compact('estados', 'tipos', 'zonas', 'calle', 'propietarios', 'tieneAccesoPropietario', 'tieneAccesoInformacionAlquiler'));
    }


    public function generarPDFlistadoEstados(Request $request)
    {
        $pertenece = $request->input('pertenece');

        if ($pertenece == 'estadosAlquiler') {

            $propiedades = $this->filtroService->aplicarFiltrosA($request->all())
                ->whereNotNull('cod_alquiler')
                ->with('calle') // <-- Agrega este filtro aquí
                ->get()
                ->sortBy(function ($propiedad) {
                    return $propiedad->calle->name ?? ''; // Cambia "name" por el campo real de calle
                });

            $usernames = DB::connection('mysql4')
                ->table('usuarios')
                ->whereIn('id', $propiedades->pluck('last_modified_by'))
                ->pluck('username', 'id');

            $pdf = SnappyPdf::loadView(
                'atencionAlCliente.propiedad.pdf.pdfListadoEstados',
                compact('propiedades', 'pertenece', 'usernames')
            )
                ->setOption('page-size', 'legal') // Tamaño de página (A4, Letter, etc.)
                ->setOption('orientation', 'landscape') // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('margin-top', 20) // Margen superior (mm)
                ->setOption('margin-bottom', 15) // Margen inferior (mm)
                ->setOption('margin-left', 5) // Margen izquierdo (mm)
                ->setOption('margin-right', 15) // Margen derecho (mm)
                ->setOption('disable-smart-shrinking', true) // Evita reducción automática
                ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
                ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
                ->setOption('footer-center', 'Page [page] of [toPage]'); // pagina x de y
            ;

            return $pdf->stream("listadoEstados.pdf");
        } elseif ($pertenece == 'estadoPropietarioA') {
            $propietarioId = $request->input('propietarioo');

            if ($propietarioId) {
                // Buscar las propiedades relacionadas con este propietario a través de propiedades_padron
                $propiedades = Propiedades_padron::where('padron_id', $propietarioId)
                    ->with([
                        'propiedad.propietarios',
                        'propiedad.fotos',
                        'propiedad.documentacion',
                        'propiedad.calle'
                    ])
                    ->get()
                    ->map(function ($pp) {
                        return $pp->propiedad;
                    })
                    ->filter(function ($propiedad) {
                        return $propiedad && $propiedad->cod_alquiler;
                    })
                    ->sortBy(function ($propiedad) {
                        return $propiedad->calle->name ?? ''; // Cambia "name" si el campo de calle es otro
                    });
            } else {
                // Si no hay propietario seleccionado, mostrar todas las propiedades en alquiler
                $propiedades = Propiedad::whereNotNull('cod_alquiler')
                    ->with([
                        'fotos',
                        'documentacion',
                        'calle',
                        'zona',
                        'tipoInmueble',
                        'precio',
                        'estadoVenta'
                    ])
                    ->get()
                    ->sortBy(function ($propiedad) {
                        return $propiedad->calle->name ?? ''; // Igual aquí
                    });
            }

            /*  dd($propiedades); */
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
        }
    }


     /* pdf de propiedad individual v/a */
    public function generarPDFpantillaPropiedad($id, $tipoBTN)
    {
        $usuario = $this->usuario;
        $propiedad = $this->propiedadService->obtenerPropiedadConId($id);

        $contratoMasNuevo = $this->propiedadService->obtenerContratoMasReciente($id);
        $idCasas = $contratoMasNuevo->id_casa ?? null;
        $vencimiento_contratos = $contratoMasNuevo->vencimiento_contrato ?? null;
        $inicio_contrato = $contratoMasNuevo->inicio_contrato ?? null;


        //$idCasas = $this->propiedadService->obtenerIdCasas($propiedad->folio);
        //$inicio_contrato = $this->propiedadService->obtenerInicioContrato($idCasas);
        //$vencimiento_contratos = $this->propiedadService->obtenerVencimientoContratos($idCasas);





        $propietarios = $this->propiedadService->obtenerPropietarios($id);
        $ultimoPrecio = $this->precioService->obtenerUltimoPrecio($id);
        $precio = $ultimoPrecio;
        $observaciones_propiedades_venta = $this->observacionesPropiedadesService->obtenerObservacionesVenta($id);
        $observaciones_propiedades_alquiler = $this->observacionesPropiedadesService->obtenerObservacionesAlquiler($id);
        $fotos = $this->fotoService->obtenerFotos($id);
        $documentos = $this->documentacionService->obtenerDocumento($id);
        $historialFecha = $this->historialFechasService->obtenerHistorialFecha($id);
        $tasacion = $this->tasacionService->obtenerUltimaTasacion($id);
        $padrones = $this->propiedad_padronService->obtenerPropietarios($id);
        $htmlRemplace = '\\\\10.10.10.151\\compartida\\PROPIEDADES\\';

        // Busca la propiedad con todas sus relaciones
        $propiedad = Propiedad::with([
            'fotos',
            'documentacion',
            'calle',
            'zona',
            'tipoInmueble',
            'precio',
        ])->findOrFail($id);

        // Determina si es alquiler o venta
        $tipo = $propiedad->cod_alquiler ? 'Alquiler' : 'Venta';

        $pdf = SnappyPdf::loadView(
            'atencionAlCliente.propiedad.pdf.pdfPlantillaPropiedad',
            compact(
                'propiedad',
                'tipo',
                'usuario',
                'propiedad',
                'idCasas',
                'inicio_contrato',
                'vencimiento_contratos',
                'propietarios',
                'ultimoPrecio',
                'precio',
                'observaciones_propiedades_venta',
                'observaciones_propiedades_alquiler',
                'fotos',
                'documentos',
                'historialFecha',
                'tasacion',
                'padrones',
                'tipoBTN',
                'htmlRemplace'
            )
        )
            ->setOption('page-size', 'a4')
            ->setOption('orientation', 'portrait')
            ->setOption('enable-local-file-access', true) // Permite acceso a archivos locales
            ->setOption('zoom', 0.8)
            ->setOption('dpi', 300)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('footer-left', 'Salas Inmobiliaria') // Texto pie izquierdo
            ->setOption('footer-font-size', 7) // Tamaño fuente pie (px)
            ->setOption('footer-center', $usuario->username)
            /* ->setOption('footer-center', 'Page [page] of [toPage]'); // pagina x de y */;
        return $pdf->stream("propiedad_{$id}.pdf");
    }



}
