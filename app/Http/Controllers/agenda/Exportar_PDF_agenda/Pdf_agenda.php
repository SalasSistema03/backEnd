<?php

namespace App\Http\Controllers\agenda\Exportar_PDF_agenda;

use App\Models\agenda\Notas;
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
use Illuminate\Support\Facades\Log;


class Pdf_agenda
{
    public function listarAgenda(Request $request)
    {
        Log::info('Llego la informacion', [$request->all()]);

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
        if (
            $request->filled('usuario') &&
            $request->usuario !== 'null'
        ) {
            $query->where('usuario_id', $request->usuario);
        }

        // FILTRO FECHA INICIO
        if (
            $request->filled('fecha_inicio') &&
            $request->fecha_inicio !== 'null'
        ) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }

        // FILTRO FECHA FIN
        if (
            $request->filled('fecha_fin') &&
            $request->fecha_fin !== 'null'
        ) {
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

        Log::info('Datos', [$datos]);

        //return response()->json($datos);
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
