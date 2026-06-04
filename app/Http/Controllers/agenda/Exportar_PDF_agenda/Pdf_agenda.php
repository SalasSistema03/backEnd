<?php

namespace App\Http\Controllers\agenda\Exportar_PDF_agenda;

use App\Models\agenda\Notas;
use App\Models\At_cl\Propiedad;
use App\Models\cliente\clientes;
use App\Services\At_cl\documentacionService;
use App\Services\At_cl\FotosService;
use App\Services\At_cl\HistorialFechasService;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\At_cl\PrecioService;
use App\Services\At_cl\PropiedadService;
use App\Services\At_cl\ObservacionesPropiedadesService;
use App\Services\At_cl\TasacionService;
use App\Services\At_cl\Propiedades_padronService;
use App\Services\At_cl\FiltrosPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\usuarios_y_permisos\Usuario;

class Pdf_agenda
{
    public function listarAgenda(Request $request)
    {


        $query = Notas::query();

        // FILTRO ESTADO
        if ($request->filled('estado')) {

            if ($request->estado == '1') {
                $query->where('activo', 1);
            } else {
                $query->where('activo', 0);
            }
        }

        // FILTRO USUARIO
        if ( $request->filled('usuario') && $request->usuario !== 'null') {
            $query->where('usuario_id', $request->usuario);
        }

        // FILTRO FECHA INICIO
        if ($request->filled('fecha_inicio') && $request->fecha_inicio !== 'null') {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        // FILTRO FECHA FIN
        if ($request->filled('fecha_fin') && $request->fecha_fin !== 'null') {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        // FILTRO SECTOR
        if ($request->filled('sector') && $request->sector !== 'null') {

            $query->whereHas('agenda', function ($q) use ($request) {

                $q->where('sector_id', $request->sector);
            });
        }

        // EJECUTAR QUERY
        $datos = $query->get();

        //si los datos de cliente_id no son null, traemos todos los datos del cliente


        //obtenemos los username
        foreach ($datos as $dato) {
            $usuario = Usuario::find($dato->usuario_id);
            $borro = Usuario::find($dato->quien_borro);
            $dato->usuario_id = $usuario ? $usuario->username : 'Desconocido';
            $dato->quien_borro = $borro ? $borro->username : 'Desconocido';

            if($dato->cliente_id != null){
                $clientedata = clientes::find($dato->cliente_id)->first();
                $dato->datos_cliente = $clientedata;
                //Log::info('clientedata', [$clientedata]);
            }
            if($dato->propiedad_id != null){
                $propiedadata = Propiedad::find($dato->propiedad_id)->first();
                $dato->datos_propiedad = $propiedadata;
            }

        }

        Log::info('Datos', [$datos]);

         $html = view('pdfs.agenda.listadoAgenda', compact('datos'))->render();

         return response()->streamDownload(function () use ($html) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->margins(10, 1, 10, 1)
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', true)
                ->setOption('headerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;"></span><span style="text-align:right;">Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>')
                /* ->landscape() */
                /* vertical */
                ->portrait()
                ->setOption('footerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:center;">'  . '</span>  <span style="text-align:right;" class="date"></span></div>')
                ->pdf();
        }, 'ficha_propiedad.pdf');

    }








    /*
    protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
        $estado_venta, $localidad, $barrio, $Propiedades, $contrato_cabecera, $observaciones_propiedades, $provincia,
        $padron, $precio,  $usuario_id, $accessService, $propiedadService, $precioService, $observacionesPropiedadesService,
        $usuario, $fotoService, $documentacionService, $historialFechasService, $tasacionService, $propiedad_padronService, $filtroService,
        $listadoAgendaService;


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
        ListadoAgendaService $listadoAgendaService
    ) {
        $this->propiedadService = $propiedadService;
        $this->precioService = $precioService;
        $this->observacionesPropiedadesService = $observacionesService;
        $this->fotoService = $fotoService;
        $this->documentacionService = $documentacionService;
        $this->historialFechasService = $historialFechasService;
        $this->tasacionService = $tasacionService;
        $this->propiedad_padronService = $propiedad_padronService;
        $this->listadoAgendaService = $listadoAgendaService;
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
        $this->historialFechasService = $historialFechasService;
        $this->tasacionService = $tasacionService;
        $this->propiedad_padronService = $propiedad_padronService;
        $this->filtroService = $filtroService;
    }


    public function index()
    {
        // Nombre de la vista correspondiente en la base de datos
        $vistaNombre = 'listadoAgenda';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        // Definimos un array con los nombres de los botones
        $btnNombres = [
            'listarAgendaAlquiler',
            'listarAgendaVenta'
        ];
        // Inicializamos un array vacío para almacenar los accesos
        $accesos = [];
        // Recorremos cada nombre de botón
        foreach ($btnNombres as $btnNombre) {
            // Verificamos si el usuario tiene acceso a cada botón y almacenamos el resultado en el array de accesos
            $accesos[$btnNombre] = $this->accessService->tieneAcceso($btnNombre);
        }
        // Asignamos el acceso a 'propietario' a una variable
        $tieneAccesoAlquiler = $accesos['listarAgendaAlquiler'];
        // Asignamos el acceso a 'InformacionAlquiler' a una variable
        $tieneAccesoVenta = $accesos['listarAgendaVenta'];


        $asesores = $this->listadoAgendaService->getAsesoresVenta();
        $alquilerAsesor = $this->listadoAgendaService->getAsesoresAlquiler();


        return view('agenda.pdf.pdfListaAgenda', compact('asesores', 'alquilerAsesor', 'tieneAccesoAlquiler', 'tieneAccesoVenta'));
    }


    public function propiedaesPorAsesorPDF(Request $request)
    {

        $pertenece = $request->input('pertenece');
        $fechaInicio = $request->input('fecha-inicio');
        $fechaFin = $request->input('fecha-fin');
        $asesorId = $request->input('asesor');
        $estado = $request->input('estado');


        if ($request->input('pertenece') == 'PropiedadesxAsesorV') {

            if ($fechaInicio == null || $fechaFin == null) {
                return redirect()->back()->with('error', 'Debe seleccionar una fecha de inicio y una fecha de fin');
            } elseif (!empty($fechaInicio) && !empty($fechaFin) && $asesorId != null) {
                $notas = $this->listadoAgendaService->obtenerNotasFiltradas($fechaInicio, $fechaFin, $asesorId, $estado, $pertenece);
            } elseif (!empty($fechaInicio) && !empty($fechaFin) && $asesorId == null) {
                $notas = $this->listadoAgendaService->obtenerNotasFiltradasSinAsesor($fechaInicio, $fechaFin, $estado, $pertenece);
            }
        } elseif ($request->input('pertenece') == 'PropiedadesxAsesorA') {

            if ($fechaInicio == null || $fechaFin == null) {
                return redirect()->back()->with('error', 'Debe seleccionar una fecha de inicio y una fecha de fin');
            } elseif (!empty($fechaInicio) && !empty($fechaFin) && $asesorId != null) {
                $notas = $this->listadoAgendaService->obtenerNotasFiltradas($fechaInicio, $fechaFin, $asesorId, $estado, $pertenece);
            } elseif (!empty($fechaInicio) && !empty($fechaFin) && $asesorId == null) {
                $notas = $this->listadoAgendaService->obtenerNotasFiltradasSinAsesor($fechaInicio, $fechaFin, $estado, $pertenece);
            }
        }
        $notas = $this->listadoAgendaService->enriquecerNotas($notas, $pertenece);

        $pdf = $this->listadoAgendaService->generarPdfListadoEstados($notas, $pertenece);
        return $pdf->stream("listadoEstados.pdf");
    } */
}
