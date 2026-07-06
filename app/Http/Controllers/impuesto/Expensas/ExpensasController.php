<?php

namespace App\Http\Controllers\impuesto\Expensas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\impuesto\EXP\ExpEdificiosService;
use App\Services\impuesto\EXP\ExpensasService;
use App\Services\impuesto\EXP\UnidadesServices;
use App\Services\impuesto\EXP\ProveedoresServices;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Exception;


class ExpensasController extends Controller
{

    protected $ExpensasService;
    protected $edificiosService;

    public function __construct()
    {
        $this->ExpensasService = new ExpensasService();
        $this->edificiosService = new ExpEdificiosService();
    }

    /*Este controlador consulata en la db de roberto las unidades  */
    public function getPadronUnidadesController()
    {
        try {
            $resultado = $this->ExpensasService->getPadronUnidadesService();
            return response()->json([
                'status' => 'success',
                'data' => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los padrones de unidades',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }


    public function filtroUnidadesCompleto(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));
            $filtros = $request->input('filtros', []);

            $data = $this->ExpensasService->filtrarUnidadesCompleto($search, $filtros);

            return response()->json([
                'status'  => 'success',
                'data'    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error inesperado al procesar las unidades. Por favor, intente nuevamente.'
            ], 500);
        }
    }

    public function completarCargaUnidadesController(Request $request)
    {
        if (empty($request->input('repetir')) || !is_array($request->input('repetir'))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El listado de unidades ("repetir") es requerido y debe ser un arreglo.'
            ], 422);
        }

        try {
            $this->ExpensasService->completarCargaUnidadesService($request->all());

            return response()->json([
                'status'  => 'success',
                'message' => 'Unidades cargadas correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un problema al guardar los cambios en el servidor.'
            ], 500);
        }
    }



    /**
     * Endpoint de acción para sincronizar/actualizar el padrón desde mysql2 a mysql9.

     */
    public function actualizarPadronUnidadesController(UnidadesServices $unidadesServices)
    {
        try {
            // 1. Ejecutamos el ETL (Extract, Transform, Load) que armamos en el servicio
            $unidadesServices->PadronUnidadesSyS();

            // 2. Respuesta HTTP 200 de éxito para Vue.js
            return response()->json([
                'status'  => 'success',
                'message' => 'El padrón de unidades se sincronizó y actualizó correctamente.'
            ], 200);
        } catch (\Exception $e) {

            // 4. Respuesta HTTP 500 controlada para que Vue.js maneje el error limpiamente
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un problema interno al intentar sincronizar el padrón con la base de datos externa.'
            ], 500);
        }
    }

    public function eliminarUnidadController(int $id)
    {
        try {
            // Delegamos la eliminación al servicio
            $this->ExpensasService->eliminarUnidadService($id);

            // Devolvemos HTTP 200 OK
            return response()->json([
                'status'  => 'success',
                'message' => 'La unidad fue eliminada correctamente.'
            ], 200);
        } catch (\Exception $e) {
            // Devolvemos HTTP 500
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un problema al intentar eliminar la unidad.'
            ], 500);
        }
    }


    /* ADMINISTRADORES */
    /**
     * Obtiene la lista de administradores para mostrar en la tabla (GET).
     */
    public function getAdministradoresController(Request $request, ProveedoresServices $proveedoresServices)
    {
        /* $usuarioId = Auth::id(); 
        $vistaNombre = 'exp-administrador-consorcio';
        $permisoService = new PermitirAccesoPropiedadService($usuarioId); */

        // Validación de permisos modo API (HTTP 403)
        /*      if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tienes acceso a esta vista.'
            ], 403);
        } */

        try {
            // Capturamos lo que el usuario escriba en el buscador de Vue
            $search = $request->input('search');

            // Usamos tu método de filtrado que ya devuelve una Colección
            $administradores = $proveedoresServices->filtrarAdministradores($search);

            return response()->json([
                'status' => 'success',
                'data'   => $administradores
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al cargar la lista de administradores.'
            ], 500);
        }
    }


    /**
     * Ejecuta la actualización masiva conectándose a la BD externa (POST).
     * Este es el método unificado que reemplaza a "create" y "CargarAdministradores".
     */
    public function sincronizarAdministradoresController(ProveedoresServices $proveedoresServices)
    {
        try {
            // Ejecutamos tu función SQL original intacta
            $proveedoresServices->actualizarPadronProveedores();

            return response()->json([
                'status'  => 'success',
                'message' => 'El padrón de administradores se actualizó correctamente desde el sistema externo.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error interno al intentar actualizar el padrón de administradores.'
            ], 500);
        }
    }


    //EDIFICIOS
    /**
     * GET: Lista y filtra los edificios (Reemplaza PadronEdificios y filtroConsorcio)
     */
    public function obtenerEdificios(Request $request)
    {
        try {
            $search = $request->input('search');
            $datos = $this->edificiosService->obtenerEdificiosFiltrados($search);

            return response()->json([
                'status' => 'success',
                'data'   => $datos
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al cargar los edificios.'], 500);
        }
    }

    /**
     * POST: Crea un edificio nuevo (Reemplaza PadronEdificiosCargar)
     */
    public function crearEdificio(Request $request)
    {
        try {
            $this->edificiosService->crearEdificio($request->all());

            return response()->json([
                'status'  => 'success',
                'message' => 'Edificio creado correctamente.'
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo crear el edificio.'], 500);
        }
    }

    /**
     * PUT: Actualiza un edificio (Reemplaza actualizarConsorcio)
     */
    public function actualizarEdificio(Request $request, $id)
    {
        try {
            $this->edificiosService->actualizarEdificio($id, $request->all());

            return response()->json([
                'status'  => 'success',
                'message' => 'Edificio actualizado correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo actualizar el edificio.'], 500);
        }
    }




    /* BROCHES */
    public function getBrochesController(Request $request)
    {
        try {
            /* // LÓGICA DE PERMISOS (Opcional, si no usas middleware)
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);
        if (!$permisoService->tieneAccesoAVista('exp-broche')) {
            return response()->json(['status' => 'error', 'message' => 'No tienes acceso a esta vista.'], 403);
        }
        */

            // Capturamos mes y año de la URL si vienen (ej: ?mes=5&anio=2026)
            $mes = $request->query('mes');
            $anio = $request->query('anio');

            // Llamamos al servicio
            $datos = $this->ExpensasService->getBrochesService($mes, $anio);

            return response()->json([
                'status' => 'success',
                'data'   => $datos
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error al cargar el padrón de broches.'
            ], 500);
        }
    }



    public function brocheExpensasBuscar(Request $request)
    {
        try {
            // request()->all() capturará cosas como: ['folio' => 123, 'edificio' => 5]
            $filtros = $request->all();

            $resultados = $this->ExpensasService->buscarUnidadesParaBroche($filtros);

            return response()->json([
                'status' => 'success',
                'data'   => $resultados
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al realizar la búsqueda de unidades.'
            ], 500);
        }
    }



    public function guardarBrocheExpensaController(Request $request)
    {
        try {
            // Llamamos al servicio pasando todos los datos
            // Asegúrate de inyectar tu servicio en el constructor del controlador
            $data = $this->ExpensasService->guardarBrocheExpensa($request->all());

            return response()->json([
                'status'  => 'success',
                'message' => 'Se guardaron los datos correctamente.',
                'data'    => $data
            ], 201); // 201 Created es ideal para POST

        } catch (Exception $e) {
            // Si el error es el código 409 que configuramos en el servicio (Duplicado)
            if ($e->getCode() === 409) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => $e->getMessage()
                ], 409);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Ocurrió un error al intentar guardar el broche.'
            ], 500);
        }
    }



    public function eliminarBrocheController($id)
    {
        try {
            $this->ExpensasService->eliminarBroche($id);

            return response()->json([
                'status'  => 'success',
                'message' => 'Broche eliminado correctamente.'
            ], 200);
        } catch (Exception $e) {
            // Identificamos si el error fue el 404 que lanzamos nosotros, u otro (500)
            $statusCode = $e->getCode() === 404 ? 404 : 500;

            return response()->json([
                'status'  => 'error',
                // Si es 404 mostramos el mensaje del servicio, si es 500 un mensaje genérico
                'message' => $statusCode === 404 ? $e->getMessage() : 'Error al eliminar el broche.'
            ], $statusCode);
        }
    }



    public function descargarBrocheExpensas(Request $request)
{
    try {
        // 1. Validamos que lleguen los datos mínimos
        $mes = $request->input('mes');
        $anio = $request->input('anio');
        $administrador = $request->input('administrador');

        // 2. Traemos los datos del servicio
        $resultado = $this->ExpensasService->obtenerDatosBrochePdf($mes, $anio, $administrador);

        // 3. Obtenemos el nombre del usuario desde el Token de la API (¡Chau session!)
        $username = auth('api')->user()->username ?? 'Usuario';

        // 4. Generamos el PDF con Snappy
        $pdf = SnappyPdf::loadView(
            'pdfs.impuestos.expensa.Carga_Broche_Expensas_Pdf', // <-- ACÁ ESTÁ EL CAMBIO
            compact('resultado')
        )
            ->setOption('page-size', 'legal')
            ->setOption('orientation', 'portrait')
            ->setOption('margin-top', 20)
            ->setOption('margin-bottom', 15)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('footer-left', 'Salas Inmobiliaria')
            ->setOption('footer-font-size', 8)
            ->setOption('footer-center', 'Page [page] of [toPage]')
            ->setOption('footer-right', $username) // Pasamos la variable $username
            ->setOption('zoom', 0.85);

        // 5. Devolvemos el PDF para que Axios (con responseType: 'blob') lo descargue
        return $pdf->download("broches_expensas_{$anio}_{$mes}.pdf");

    } catch (\Exception $e) {
        // En vez de un mensaje genérico, le decimos a Laravel que nos escupa el error exacto y la línea
        return response()->json([
            'status' => 'error',
            'message' => 'ERROR REAL: ' . $e->getMessage(),
            'linea' => $e->getLine(),
            'archivo' => $e->getFile()
        ], 500);
    }
}
}