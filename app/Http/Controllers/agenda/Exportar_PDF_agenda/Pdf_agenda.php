<?php

namespace App\Http\Controllers\agenda\Exportar_PDF_agenda;

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
use App\Services\At_cl\FiltrosPdfService;
use Illuminate\Http\Request;
use App\Services\agenda\ListadoAgendaService;

class Pdf_agenda
{
    protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
        $estado_venta, $localidad, $barrio, $Propiedades, $contrato_cabecera, $observaciones_propiedades, $provincia,
        $padron, $precio,  $usuario_id, $accessService, $propiedadService, $precioService, $observacionesPropiedadesService,
        $usuario, $fotoService, $documentacionService, $historialFechasService, $tasacionService, $propiedad_padronService, $filtroService,
        $listadoAgendaService;

    /**
     * Constructor del controlador Pdf_agenda
     *
     * Inyecta los servicios necesarios y establece el contexto del usuario autenticado.
     *
     * @param PropiedadService               $propiedadService       servicio de gestión de propiedades
     * @param PrecioService                  $precioService          servicio de gestión de precios
     * @param ObservacionesPropiedadesService $observacionesService  servicio de observaciones de propiedades
     * @param FotosService                   $fotoService            servicio de gestión de fotos
     * @param documentacionService           $documentacionService   servicio de documentación
     * @param HistorialFechasService         $historialFechasService servicio de historial de fechas
     * @param TasacionService                $tasacionService        servicio de tasaciones
     * @param Propiedades_padronService      $propiedad_padronService servicio de padrón de propiedades
     * @param FiltrosPdfService              $filtroService          servicio de filtros PDF
     * @param ListadoAgendaService           $listadoAgendaService   servicio de listado de agenda
     *
     * @return void
     * @access public
     */
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

    /**
     * Muestra la vista inicial del listado de agenda en PDF
     *
     * Obtiene los asesores de venta y alquiler para ser utilizados como filtros.
     *
     * @return \Illuminate\View\View vista del listado de agenda en PDF
     * @access public
     */
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

    /**
     * Genera un PDF de propiedades filtradas por asesor
     *
     * Procesa filtros por tipo, fechas, asesor y estado, y genera un PDF
     * con el listado correspondiente.
     *
     * @param Request $request solicitud HTTP con los filtros seleccionados
     *
     * @return mixed respuesta PDF en streaming
     * @access public
     */
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
    }
}
