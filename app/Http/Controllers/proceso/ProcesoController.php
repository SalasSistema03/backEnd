<?php

namespace App\Http\Controllers\proceso;

use App\Http\Controllers\Controller;
use App\Services\proceso\ProcesoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\contrato\ProcesoContratoService;
use App\Services\contable\sellado\RegistroSelladoService;
use App\Services\Dpto_Tecnico\ProcesoDptoTecnicoService;
use App\Services\proceso\RepactadoService;

/**
 * Controlador del módulo de procesos: reservas de alquiler, estados de contrato,
 * inventario del Departamento Técnico y repactado.
 *
 * Cada método expone una ruta de routes/api.php y delega la lógica de negocio
 * en los services (ProcesoService, ProcesoContratoService, ProcesoDptoTecnicoService,
 * RepactadoService).
 */
class ProcesoController extends Controller
{
    protected $reservaService;
    protected RegistroSelladoService $registroSelladoService;
    protected ProcesoContratoService $procesoContratoService;


    /**
     * Inyecta los services que el controlador necesita. El contenedor de Laravel
     * resuelve estas clases al instanciar el controlador en cada petición.
     */
    public function __construct(ProcesoService $reservaService, RegistroSelladoService $registroSelladoService, ProcesoContratoService $procesoContratoService)
    {
        $this->reservaService = $reservaService;
        $this->registroSelladoService = $registroSelladoService;
        $this->procesoContratoService = $procesoContratoService;
    }

    /**
     * Registra una nueva reserva de alquiler (POST /subir-reservas).
     *
     * Envía todos los campos del formulario al service, junto con el ID del usuario
     * autenticado vía guard api. Si viene un archivo en 'comprobante', se agrega al
     * arreglo para que el service lo guarde en la carpeta compartida de reservas.
     * Toda la operación (historial, proceso y cambio de estado de la propiedad)
     * ocurre dentro de la transacción: ante cualquier excepción se revierte todo.
     *
     * @return \Illuminate\Http\JsonResponse 201 con la ruta donde quedó guardado el comprobante.
     */
    public function subirReservas(Request $request)
    {
        DB::beginTransaction();
        try {

            $usuarioId = auth('api')->id();
            $data = $request->all();

            // El service recibe el archivo dentro del arreglo $data para persistirlo;
            // hasta acá $request->all() no incluye los archivos subidos.
            if ($request->hasFile('comprobante')) {
                $data['comprobante'] = $request->file('comprobante');
            }

            $result = $this->reservaService->subirReserva($data, $usuarioId);

            DB::commit();
            return response()->json([
                'success' => true,
                'path' => $result['comprobantePath']
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error subirReservas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista las reservas del usuario (GET /obtener-reservas).
     *
     * Devuelve los historiales de estado de reserva activos (los que están apuntados
     * por un proceso_propiedad), filtrados opcionalmente por estado y por mes.
     * El service aplica el control de acceso: si el usuario no tiene el permiso
     * 'listarAsesoresReserva', solo ve las reservas de las que es asesor.
     *
     * @return \Illuminate\Http\JsonResponse colección en 'resultado'.
     */
    public function obtenerReservas(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();
            $estado = $request->input('estado');
            $mes = $request->input('mes');
            $reservas = $this->reservaService->obtenerReservas($usuarioId, $estado, $mes);

            return response()->json(['resultado' => $reservas]);
        } catch (\Exception $e) {
            Log::error('Error obtenerReservas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener reservas.'], 500);
        }
    }

    /**
     * Cambia el estado de una reserva (POST /guardar-estado).
     *
     * El service crea un nuevo registro en historial_estado_reserva y actualiza el
     * puntero del proceso_propiedad. Reglas de negocio que se disparan según el estado:
     * - Estado 3 (reserva finalizada): genera los historiales de Contratos Nuevos y
     *   Departamento Técnico, registra la fecha de firma y notifica a los usuarios de
     *   ambos sectores.
     * - Estado 4 (reserva caída): restaura el estado de alquiler de la propiedad al
     *   valor que tenía antes de la reserva.
     *
     * @return \Illuminate\Http\JsonResponse historial recién creado en 'data'.
     */
    public function guardarEstado(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();
            $data = $request->all();

            $historial = $this->reservaService->guardarEstado($data, $usuarioId);

            return response()->json(['success' => true, 'data' => $historial]);
        } catch (\Exception $e) {
            Log::error('Error guardarEstado: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar el estado.'], 500);
        }
    }

    /**
     * Historial completo de una reserva (GET /getHistorialReservaAlquiler?id=<idProceso>).
     *
     * Combina en una sola respuesta tres fuentes de historial del mismo proceso:
     * - 'resultado': historial de estados de reserva (con el nombre de usuario de quien cargó).
     * - 'historialContratoNuevos': observaciones registradas en historial_estado_contrato,
     *   con nombre de usuario y de estado resueltos.
     * - 'historialDptoNuevos': comentarios del inventario del Departamento Técnico.
     */
    public function getHistorial(Request $request)
    {
        try {
            $idProceso = $request->input('id');
            $historial = $this->reservaService->getHistorial($idProceso);
            $historialContratoNuevos = (new ProcesoContratoService())->getObservacionesContratoNuevo($idProceso);
            $historialDptoNuevos = (new ProcesoDptoTecnicoService())->getComentarioInventario($idProceso);


            return response()->json(['resultado' => $historial, 'historialContratoNuevos' => $historialContratoNuevos, 'historialDptoNuevos' => $historialDptoNuevos]);
        } catch (\Exception $e) {
            Log::error('Error getHistorial: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener historial.'], 500);
        }
    }

    /**
     * Lista las reservas identificadas (GET /getReservaIdentificada?data=<monto>).
     *
     * Devuelve los historiales de reserva de los procesos cuya reserva es de tipo
     * TRANSFERENCIA y que tienen monto_aceptado igual al monto recibido en 'data'.
     * El parámetro es opcional en el service: sin él traería todas las transferencias.
     *
     * @return \Illuminate\Http\JsonResponse colección en 'resultado'.
     */
    public function getReservaIdentificadas(Request $request)
    {
        try {
            $montoAceptado = $request->input('data');
            $reservas = $this->reservaService->getReservasIdentificadas($montoAceptado);

            return response()->json(['resultado' => $reservas]);
        } catch (\Exception $e) {
            Log::error('Error getReservaIdentificadas: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener reservas identificadas.'], 500);
        }
    }

    /**
     * Marca una reserva como identificada (POST /guardarReservaIdentificada).
     *
     * Fija monto_aceptado = 1 en el proceso_propiedad indicado y registra el usuario
     * que realiza la modificación. Responde 404 si el proceso no existe.
     *
     * @return \Illuminate\Http\JsonResponse success true, o error 404 si no hay proceso.
     */
    public function guardarReservaIdentificada(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();
            $idProceso = $request->input('id');

            $result = $this->reservaService->guardarReservaIdentificada($idProceso, $usuarioId);

            if (!$result) {
                return response()->json(['error' => 'Proceso no encontrado'], 404);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error guardarReservaIdentificada: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar la reserva.'], 500);
        }
    }

    /**
     * Devuelve el comprobante de una reserva (POST /alquiler/obtener-comprobante).
     *
     * Recibe en 'documentacion' la ruta completa del archivo almacenado (guardada en
     * la carpeta compartida al crear la reserva). El service lee el archivo y detecta
     * el Content-Type según la extensión; acá se lo envía con Content-Disposition
     * 'inline' para que el navegador lo muestre sin descargarlo.
     *
     * @return \Illuminate\Http\Response contenido binario del archivo.
     */
    public function obtenerComprobante(Request $request)
    {
        try {
            $ruta = $request->input('documentacion');
            $comprobante = $this->reservaService->obtenerComprobante($ruta);

            return response($comprobante['content'])
                ->header('Content-Type', $comprobante['contentType'])
                ->header('Content-Disposition', 'inline; filename="' . $comprobante['filename'] . '"');
        } catch (\Exception $e) {
            Log::error('Error obtenerComprobante: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Catálogo de estados de contrato (GET /getEstadoProcesoContrato).
     *
     * Devuelve todos los registros de la tabla estado_contrato, usados por el
     * frontend para poblar los selectores de estado del módulo de contratos.
     */
    public function getEstadosContrato()
    {
        try {

            $estados = (new ProcesoContratoService())->EstadosContrato();

            return response()->json(['resultado' => $estados]);
        } catch (\Exception $e) {
            Log::error('Error getEstadosContrato: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener estados del contrato.'], 500);
        }
    }

    /**
     * Listado de procesos con contrato (GET /getHistorialContrato).
     *
     * Devuelve los proceso_propiedad que ya tienen historial de contrato, junto con
     * sus relaciones (propiedad, cliente, asesor, historiales de reserva/contrato/dpto
     * y registro de sellado). Filtros opcionales que acepta el arreglo enviado:
     * 'mes' y 'anio' (sobre fecha_reserva), 'filtroEstado', 'filtroAsesor' y 'folio'.
     * Además, si el historial no tiene gastos_administrativos, el service lo completa
     * desde el registro de sellado correspondiente al folio.
     *
     * @param  array $form Datos del request; el service espera mes, anio, folio, etc.
     * @return \Illuminate\Http\JsonResponse colección en 'resultado'.
     */
    public function getHistorialContrato(Request $request)
    {
        try {
            $historial = (new ProcesoContratoService())->getHistorialContrato($request->all());

            return response()->json(['resultado' => $historial]);
        } catch (\Exception $e) {
            Log::error('Error getHistorialContrato: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener el historial.'], 500);
        }
    }


    /**
     * Actualiza el estado del contrato de un proceso (POST /ActualizarEstadoContrato).
     *
     * El service orquesta toda la operación dentro de una transacción: crea el
     * historial de estado de contrato, actualiza el proceso (meses, precio, puntero
     * al historial), registra el sellado si el request trae datos de folio, y según
     * el id_estado puede cancelar o reactivar la reserva asociada y notificar al
     * asesor. El estado 6 además restaura el estado de alquiler de la propiedad y
     * cambia el estado del último historial de inventario del Departamento Técnico a 4.
     *
     * @return \Illuminate\Http\JsonResponse el proceso actualizado en 'data'.
     */
    public function ActualizarEstadoContrato(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();

            $proceso = $this->procesoContratoService->actualizarEstadoContrato($request->all(), $usuarioId);

            return response()->json(['success' => true, 'data' => $proceso]);
        } catch (\Exception $e) {
            Log::error('Error ActualizarEstadoContrato: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Observaciones del historial de contrato nuevo (GET /getObservacionesContratoNuevo).
     *
     * Devuelve las observaciones registradas en historial_estado_contrato, cada una
     * con el nombre de quien la cargó y el nombre del estado resuelto.
     */
    public function getObservacionesContratoNuevo(Request $request)
    {
        try {
            $observaciones = (new ProcesoContratoService())->getObservacionesContratoNuevo($request->all());

            return response()->json(['resultado' => $observaciones]);
        } catch (\Exception $e) {
            Log::error('Error getObservacionesContratoNuevo: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener observaciones del contrato.'], 500);
        }
    }



    // ------------------------------------------------------------------
    // Departamento Técnico: inventario de propiedades
    // ------------------------------------------------------------------

    /**
     * Historial de inventario del Departamento Técnico (GET /getHistorialInventario).
     *
     * Devuelve los proceso_propiedad que ya tienen historial de inventario, con sus
     * relaciones (propiedad, cliente, asesor, historiales de reserva/contrato/dpto y
     * registro de sellado). Filtros opcionales: 'mes' y 'anio' (sobre fecha_reserva),
     * 'folio' y 'filtroEstado'/'filtroAsesor' (sobre historial_estado_dpto). Cuando no
     * se filtra por estado, el service ordena por el estado del inventario actual.
     */
    public function getHistorialInventario(Request $request)
    {
        // El frontend puede enviar el filtro como campo 'form' o como el request
        // completo. Si llega como lista con un solo elemento (form[0]), se extrae
        // ese elemento para que el service reciba un arreglo plano con las claves
        // mes, anio, folio, filtroAsesor y filtroEstado.
        $form = $request->input('form', $request->all());

        if (is_array($form) && isset($form[0]) && is_array($form[0])) {
            $form = $form[0];
        }

        try {
            $historial = (new ProcesoDptoTecnicoService())->getHistorialInventario($form);

            return response()->json(['resultado' => $historial]);
        } catch (\Exception $e) {
            Log::error('Error getHistorialInventario: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener el historial.'], 500);
        }
    }

    /**
     * Usuarios del Departamento Técnico (GET /getUsuariosDpto).
     *
     * Devuelve los registros de usuario_sector con el flag dpto = 'S', junto con el
     * usuario asociado; se usa para poblar el selector de verificadores de inventario.
     */
    public function getUsuariosDpto(Request $request)
    {
        try {
            $usuarios = (new ProcesoDptoTecnicoService())->getUsuariosDpto($request);

            return response()->json(['resultado' => $usuarios]);
        } catch (\Exception $e) {
            Log::error('Error getUsuariosDpto: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener los usuarios.'], 500);
        }
    }

    /**
     * Catálogo de estados de inventario (GET /getEstadoDpto).
     *
     * Devuelve todos los registros de la tabla estado_dpto.
     */
    public function getEstadoDpto(Request $request)
    {
        try {
            $estados = (new ProcesoDptoTecnicoService())->getEstadoDpto();

            return response()->json(['resultado' => $estados]);
        } catch (\Exception $e) {
            Log::error('Error getEstadoDpto: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al obtener los estados.'], 500);
        }
    }

    /**
     * Registra un nuevo movimiento de inventario (POST /ActualizarInventario).
     *
     * El service valida que exista el historial indicado en 'inventario_id' y crea un
     * nuevo registro en historial_estado_dpto con el estado recibido; la fecha_inventario
     * solo se persiste cuando el estado es 3 o 4. Luego actualiza el puntero del proceso
     * hacia ese nuevo historial. Si el estado es 4, restaura el estado de alquiler
     * de la propiedad y deja el último historial de contrato en estado 6.
     *
     * Nota: el service devuelve su propia respuesta JSON (200 o 404), por lo que el
     * objeto resultante queda envuelto dentro de 'data'.
     */
    public function ActualizarInventario(Request $request)
    {
        try {
            $usuarioId = auth('api')->id();

            $proceso = (new procesoDptoTecnicoService())->actualizarInventario($request, $usuarioId);

            return response()->json(['success' => true, 'data' => $proceso]);
        } catch (\Exception $e) {
            Log::error('Error ActualizarInventario: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Comentarios del inventario (GET /getComentariosInventario?id=<idProceso>).
     *
     * Devuelve todos los registros de historial_estado_dpto del proceso indicado,
     * con el usuario que cargó cada comentario y el estado asociado. A diferencia de
     * getHistorialInventario, no filtra por el historial apuntado por el proceso:
     * devuelve el historial completo.
     */
    public function getComentarios(Request $request)
    {
        try {

            $proceso = (new procesoDptoTecnicoService())->getComentarioInventario($request->id);

            return response()->json(['success' => true, 'data' => $proceso]);
        } catch (\Exception $e) {
            Log::error('Error al traer Comentarios: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------------------------
    // Repactado
    // ------------------------------------------------------------------

    /**
     * Repactados del mes (GET /getRepactadoDelMes?mes=&anio=&empresa=).
     *
     * Delega en RepactadoService el armado del listado de repactados según mes, año y empresa.
     */
    public function getRepactadoDelMes(Request $request)
    {
        try {
            $resultado = (new RepactadoService())->getRepactadoDelMes($request);

            return response()->json($resultado);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error getRepactadoDelMes: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al cargar los repactados.',
                'error' => 'Ocurrió un error al cargar los repactados.',
            ], 500);
        }
    }

    /**
     * Repactados filtrados (GET /getRepactadoFiltrado?mes=&anio=&empresa=&folio=).
     *
     * Delega en RepactadoService el armado de las filas del listado según mes, año,
     * empresa y folio opcional.
     */
    public function getRepactadoFiltrado(Request $request)
    {
        try {
            $filas = (new RepactadoService())->getRepactadoFiltrado($request);

            return response()->json([
                'success' => true,
                'data' => $filas,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error getRepactadoFiltrado: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al filtrar los repactados.',
                'error' => 'Ocurrió un error al filtrar los repactados.',
            ], 500);
        }
    }

    /**
     * Estados de repactado (GET /getEstadosRepactado).
     *
     * Devuelve el catálogo estado_repactado para el select del modal.
     */
    public function getEstadosRepactado()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => (new RepactadoService())->getEstados(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getEstadosRepactado: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al obtener los estados de repactado.',
                'error' => 'Ocurrió un error al obtener los estados de repactado.',
            ], 500);
        }
    }

    /**
     * Actualiza un repactado (PUT /updateRepactado).
     *
     * Delega en RepactadoService la actualización de los campos editables y el
     * cálculo del total y de la fecha de cierre.
     */
    public function updateRepactado(Request $request)
    {
        try {
            $resultado = (new RepactadoService())->updateRepactado($request);

            return response()->json($resultado);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error updateRepactado: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el repactado.',
                'error' => 'Ocurrió un error al actualizar el repactado.',
            ], 500);
        }
    }
}
