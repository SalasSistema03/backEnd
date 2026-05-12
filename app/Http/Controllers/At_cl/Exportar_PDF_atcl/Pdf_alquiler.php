<?php

namespace App\Http\Controllers\At_cl\Exportar_PDF_atcl;

use App\Models\At_cl\Calle;
use App\Models\At_cl\Estado_alquiler;
use App\Models\At_cl\Padron;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Propiedades_padron;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Zona;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\At_cl\documentacionService;
use App\Services\At_cl\FiltrosPdfService;
use App\Services\At_cl\FotosService;
use App\Services\At_cl\HistorialFechasService;
use App\Services\At_cl\ObservacionesPropiedadesService;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\At_cl\PrecioService;
use App\Services\At_cl\Propiedades_padronService;
use App\Services\At_cl\PropiedadService;
use App\Services\At_cl\TasacionService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\contable\sellado\PermitirAccesoSelladoService;

use function PHPUnit\Framework\isEmpty;

class Pdf_alquiler
{
    /* protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
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
    public function generarPDFpantillaPropiedad($id, $tipoBTN)
    {


       $propiedad = Propiedad::findOrFail($id);
       $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .titulo { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
                .campo { margin-bottom: 8px; }
                label { font-weight: bold; }
                hr { border: 1px solid #ccc; }
            </style>
        </head>
        <body>
            <div class='titulo'>FICHA DE ALQUILER</div>
            <hr>
            <div class='campo'><label>Código:</label> {$propiedad->id}</div>

        </body>
        </html>
    ";

    $pdf = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html);

    return $pdf->stream("ficha_alquiler_{$id}.pdf");

    }

 */

    public function listadoPropiedad(Request $request)
    {

        Log::info('request', $request->toArray());
        $informacionMostrar = $request->informacionMostrar;
        $calle_id = $request->calle;
        $zona_id = [];
        if ($request->zona_id != null || ! isEmpty($request->zona_id)) {
            foreach ($request->zona_id as $zona) {
                $zona_id[] = $zona;
            }
        }
        $tipo = [];
        if ($request->tipo != null || ! isEmpty($request->tipo)) {
            foreach ($request->tipo as $t) {
                $tipo[] = $t;
            }
        }

        $estado = $request->estado_id;
        $importe_minimo = $request->importe_minimo;
        $importe_maximo = $request->importe_maximo;
        $pertenece = $request->pertenece;
        $orden = $request->orden;

        $propiedades = collect();
        $username = '-';

        if ($pertenece === 'listadoPropiedadesAlquiler') {

            //Aplicamos los filtros por defecto
            $propiedades = (new FiltrosPdfService)->aplicarFiltrosA($request->all())
                ->whereNotNull('cod_alquiler')
                ->with('calle') // <-- Agrega este filtro aquí
                ->get()
                ->sortBy(function ($propiedad) {
                    return $propiedad->calle->name ?? ''; // Cambia "name" por el campo real de calle
                });

            //Obtenemos los usernames de los usuarios que han modificado las propiedades
            $modifierIds = $propiedades->pluck('last_modified_by')->filter()->unique()->values();
            $usernamesById = $modifierIds->isNotEmpty()
                ? Usuario::whereIn('id', $modifierIds)->pluck('username', 'id')->all()
                : [];

            //Asignamos el username a cada propiedad
            foreach ($propiedades as $propiedad) {
                $modId = $propiedad->last_modified_by;
                $propiedad->username = ($modId && isset($usernamesById[$modId]))
                    ? $usernamesById[$modId]
                    : '-';
            }






            //Obtenemos el username del usuario actual
            $usuario_id = auth('api')->id();

            // 1. Instanciar el servicio de permisos localmente
           $accessService = new PermitirAccesoSelladoService($usuario_id);
           $botones = [];
           $botones = [
                'listarPropiedadesAlquiler' => $accessService->tieneAcceso('listarPropiedadesAlquiler')
            ];



            $authUser = $usuario_id ? Usuario::find($usuario_id) : null;
            $username = $authUser->username ?? '-';
            // Orden final: precios por valor; estado/tipo/zona/calle por FK asc; código por cod_alquiler asc
            if ($orden !== null && $orden !== '') {
                if ($orden === 'precio_asc') {
                    $propiedades = $propiedades->sortBy(function ($propiedad) {
                        return $propiedad->precio?->moneda_alquiler_pesos

                            ?? 0;
                    });
                } elseif ($orden === 'precio_desc') {
                    $propiedades = $propiedades->sortByDesc(function ($propiedad) {
                        return $propiedad->precio?->moneda_alquiler_pesos
                            ?? 0;
                    });
                } elseif ($orden === 'estado') {
                    $propiedades = $propiedades->sortBy('id_estado_alquiler');
                } elseif ($orden === 'tipo') {
                    $propiedades = $propiedades->sortBy('id_inmueble');
                } elseif ($orden === 'zona') {
                    $propiedades = $propiedades->sortBy('id_zona');
                } elseif ($orden === 'calle') {
                    $propiedades = $propiedades->sortBy('id_calle');
                } elseif ($orden === 'codigo') {
                    $propiedades = $propiedades->sortBy('cod_alquiler');
                }
            }
        }
        // Generamos el HTML usando una vista de Blade limpia
        $html = view('pdfs.atcl.listadoPropiedad', compact('propiedades', 'username', 'informacionMostrar','botones'))->render();

        return response()->streamDownload(function () use ($html, $username) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                //quiero poner vertical o horizontal
                ->margins(10, 1, 10, 1)
                //->emulateMedia('screen')
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', true)
                //hoja actual a la derecha
                ->setOption('headerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Ficha de Propiedad</span><span style="text-align:right;">Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
                ->landscape() // 'portrait' (vertical) o 'landscape' (horizontal)
                ->setOption('footerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:center;">' . $username . '</span>  <span style="text-align:right;" class="date"></span></div>')
                ->pdf();
        }, 'ficha_propiedad.pdf');
    }
}
