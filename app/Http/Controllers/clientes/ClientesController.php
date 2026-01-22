<?php

namespace App\Http\Controllers\clientes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\At_cl\Usuario;
use App\Services\At_cl\PermitirAccesoPropiedadService;

use App\Services\clientes\ClientesService;
use App\Services\clientes\CriterioBusquedaVentaService;
use App\Services\clientes\TipoInmuebleService;
use App\Services\clientes\ZonasService;
use App\Services\clientes\UsuarioSectorService;
use App\Services\clientes\ConsultaPropVentaService;
use App\Services\clientes\HistorialCodigoConsultaService;
use App\Services\clientes\Permisos;
use App\Services\clientes\EnvioMailService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\agenda\RecordatorioController;

use function Pest\Laravel\json;

class ClientesController extends Controller
{
    protected $clienteService;

    protected $tipoInmuebleService;
    protected $criterioBusquedaService;
    protected $criterioBusquedaVentaService;
    protected $consultaPropiedadVentaService;
    protected $zonaService;
    protected $usuarioSectorService;
    protected $historialCodigoConsultaService;
    protected $usuario_id;
    protected $usuario;
    protected $permisoService;
    protected $envioMailService;
    protected $recordatorioController;

    public function __construct(
        ClientesService $clientes,
        TipoInmuebleService $tipoInmueble,
        CriterioBusquedaVentaService $criterioBusquedaVenta,
        ZonasService $zona,
        UsuarioSectorService $usuarioSectorService,
        ConsultaPropVentaService $consultaPropiedadVentaService,
        HistorialCodigoConsultaService $historialCodigoConsultaService,
        Permisos $permisoService,
        EnvioMailService $envioMailService,
        RecordatorioController $recordatorioController
    ) {
        $this->clienteService = $clientes;
        $this->tipoInmuebleService = $tipoInmueble;
        $this->criterioBusquedaVentaService = $criterioBusquedaVenta;
        $this->zonaService = $zona;
        $this->usuarioSectorService = $usuarioSectorService;
        $this->consultaPropiedadVentaService = $consultaPropiedadVentaService;
        $this->historialCodigoConsultaService = $historialCodigoConsultaService;
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);
        $this->permisoService = $permisoService;
        $this->envioMailService = $envioMailService;
        $this->recordatorioController = $recordatorioController;
    }

    public function index()
    {
        $usuario = $this->usuario;

        //Verifica los permisos del usuario para acceder a la vista de cargar cliente
        $vistaCargarCliente = 'cargarcliente';
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar permisos de botones (Seleccionar el asesor)
        $permisoBoton = "seleccionarAsesor";
        $resultadoPermisoBoton = $this->permisoService->verificarAccesoBotones_Elementos($permisoBoton);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaCargarCliente)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        $tipoInmuebles = $this->tipoInmuebleService->getTipoInmueble();
        $zonas = $this->zonaService->getAllZonas();
        $usuarioSectors = $this->usuarioSectorService->getAllUsuarioSector();
        return view('clientes.cargcarCliente.index', compact('tipoInmuebles', 'zonas', 'usuarioSectors', 'resultadoPermisoBoton', 'usuario'));
    }

    /**
     * Guarda un nuevo cliente y sus criterios asociados
     *
     * Valida los datos del formulario, crea un recordatorio si corresponde,
     * inicia una transacción para persistir el cliente, criterios de venta y
     * registro de consultas relacionadas, y envía notificaciones.
     * Devuelve un JSON de éxito o redirige con mensajes de error según corresponda.
     *
     * @param  \Illuminate\Http\Request $request  datos enviados por el formulario de cliente
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse devuelve un JSON con resultado de la operación o redirige en caso de error
     * @throws \Illuminate\Database\QueryException      si ocurre un error de consulta en la base de datos
     * @throws \Exception                                si ocurre un error general durante el proceso
     * @access public
     */
    public function guardar(Request $request)
    {

        try {
            $request->validate([
                'cliente.nombre'        => ['required', 'string', 'max:255', 'regex:/^[^\'"]+$/u'],
                'cliente.telefono'      => 'required|string|max:20',
                'cliente.observaciones' => 'nullable|string|max:500',
                'cliente.ingreso'       => 'required|string|max:100',
                'cliente.id_asesor'     => 'required|integer',
                'cliente.usuario_id'    => 'required|integer',
            ]);


            $cliente = null;
            $criteriosVentaCreados = [];
            $propiedadesVentaInput = $request->input('propiedades_venta', []);


            //Log::info('Logs provenientes de clientescontroller ', $request->all());
            $this->recordatorioController->storeDesdeClientes($request);

            DB::connection('mysql5')->transaction(function () use ($request, &$cliente, &$criteriosVentaCreados, $propiedadesVentaInput) {
                // 1. GUARDAR EL CLIENTE
                $clienteData = $request->input('cliente');
                if ($clienteData['sector_asesor'] === 'venta') {
                    $clienteData['id_asesor_venta'] = $clienteData['id_asesor'] ?? null;
                }

                $cliente = $this->clienteService->guardarcliente($clienteData);

                // 2. GUARDAR TODOS LOS CRITERIOS DE VENTA
                $criteriosVenta = $request->input('criterios_venta', []);
                log::info('Criterios de venta: ' . json_encode($criteriosVenta));
                foreach ($criteriosVenta as $criterio) {

                    $criterio['id_cliente'] = $cliente->id_cliente;
                    $criterio['usuario_id'] = $cliente->usuario_id;
                    $criterio['fecha_criterio_venta'] = $criterio['fecha_criterio'] ?? now();

                    $validator = Validator::make($criterio, [
                        'id_cliente' => 'required|integer',
                        'id_tipo_inmueble' => 'required|integer'
                    ]);

                    if ($validator->fails()) {
                        throw new \Exception('Completa los datos del venta');
                    }

                    $criterioVenta = $this->criterioBusquedaVentaService->guardarcriterioBusquedaVenta($criterio);

                    // Almacenar el criterio creado con el ID real de la base de datos
                    if (isset($criterio['id_propiedad'])) {
                        $criteriosVentaCreados[] = [
                            'id_criterio_venta' => $criterioVenta->id_criterio_venta,
                            'id_tipo_inmueble' => $criterio['id_tipo_inmueble'],
                            'cant_dormitorios' => $criterio['cant_dormitorios'],
                            'id_propiedad' => $criterio['id_propiedad']
                        ];
                    }
                }


                // 4. GUARDAR EL HISTORIAL DE CONSULTAS (DESPUÉS de que todo lo demás está creado)
                foreach ($propiedadesVentaInput as $propiedad) {
                    $propiedad['id_cliente'] = $cliente->id_cliente;
                    $propiedad['usuario_id'] = $cliente->usuario_id;
                    $propiedad['fecha_consulta_propiedad'] = $propiedad['fecha_consulta'] ?? now();
                    $propiedad['estado_consulta_venta'] = "Activo";
                    if (isset($propiedad['id_propiedad'])) {
                        foreach ($criteriosVentaCreados as $criterioCreado) {
                            log::info('Comparando propiedad: ' . json_encode($propiedad) . ' con criterio creado: ' . json_encode($criterioCreado));
                            if ($propiedad['id_tipo_inmueble'] == $criterioCreado['id_tipo_inmueble'] && $propiedad['cant_dormitorios'] == $criterioCreado['cant_dormitorios']) {
                                $propiedad['id_criterio_venta'] = $criterioCreado['id_criterio_venta'];
                                $this->consultaPropiedadVentaService->guardarConsultaPropVenta($propiedad);
                                $this->historialCodigoConsultaService->guardarHistorialCodigoConsulta($propiedad['id_propiedad'], $criterioCreado['id_criterio_venta']);

                                break;
                            } else {
                                // Eliminar campos que no existen en la tabla antes de guardar
                                unset($propiedad['id_tipo_inmueble'], $propiedad['cant_dormitorios']);
                                $this->historialCodigoConsultaService->guardarHistorialCodigoConsulta($propiedad['id_propiedad'], $criterioCreado['id_criterio_venta']);


                                $this->consultaPropiedadVentaService->guardarConsultaPropVenta($propiedad);
                            }
                        }
                    }
                }

                $this->envioMailService->enviarNuevoMail($criteriosVenta, $cliente->id_cliente, $propiedadesVentaInput);
            });
            return response()->json(['success' => true, 'message' => 'Cliente y criterios guardados correctamente']);
        } catch (QueryException $e) {
            // ... (Manejo de errores) ...
        } catch (\Exception $e) {
            // ... (Manejo de errores) ...
        }
    }


    public function clientePorTelefono($telefono = null)
    {
        $usuario = $this->usuario;

        $vistaBuscarCliente = 'cliente.telefono';
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);


        // Verificar permisos de botones (Seleccionar el asesor)
        $permisoBoton = "seleccionarAsesor";
        $resultadoPermisoBoton = $this->permisoService->verificarAccesoBotones_Elementos($permisoBoton);


        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaBuscarCliente)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }
        $tipoInmuebles = $this->tipoInmuebleService->getTipoInmueble();

        $zonas = $this->zonaService->getAllZonas();

        $usuarioSectors = $this->usuarioSectorService->getAllUsuarioSector();
        $cliente = null;

        if ($telefono) {
            $cliente = $this->clienteService->clientePorTelefonoService($telefono);


            // Si es una petición AJAX, devolvemos JSON
            if (request()->ajax()) {
                if (!$cliente) {
                    return response()->json(['error' => 'Cliente no encontrado'], 404);
                }

                // devolvés solo los datos que necesitás, por ejemplo:
                return response()->json([
                    'cliente' => $cliente,
                ]);
            }
        }
        //log::info('Cliente buscado por teléfono: ' . $cliente);

        // Si NO es AJAX, devolvés la vista normalmente (como hasta ahora)
        return view('clientes.buscarCliente.index', compact('cliente', 'tipoInmuebles', 'zonas', 'usuarioSectors', 'resultadoPermisoBoton', 'usuario'));
    }



    /* Modifcar datos personales del cliente POR ID */
    public function modificarDatosPersonales(Request $request, $id)
    {

        $request->validate([
            'telefono' => [
                'required',
                'string',
                Rule::unique('mysql5.clientes', 'telefono')->ignore($id, 'id_cliente'),
            ],
        ]);

        if ($request->pertenece_a_inmobiliaria === 'S' && is_null($request->nombre_de_inmobiliaria)) {
            log::info('El cliente pertenece a una inmobiliaria pero no se proporcionó un nombre.');
            return redirect()->back()->withErrors(['nombre_de_inmobiliaria' => 'El nombre de la inmobiliaria es obligatorio si pertenece a una.']);
        }


        $data = $request->only([
            'nombre',
            'telefono',
            'observaciones',
            'ingreso_por',
            'pertenece_a_inmobiliaria',
            'nombre_de_inmobiliaria',
            'id_asesor_venta'
        ]);

        if ($data['pertenece_a_inmobiliaria'] === 'N') {
            $data['nombre_de_inmobiliaria'] = '';
        }
        //dd($data);
        $this->clienteService->actualizarCliente($data, $id);
        //dd($data);
        return redirect()->route('cliente.telefono', ['telefono' => $data['telefono']])
            ->with('success', 'Cliente actualizado correctamente.');
    }


    /**
     * Guarda criterios de búsqueda y propiedades asociadas para un cliente
     *
     * Este método registra los criterios de búsqueda de venta y las propiedades asignadas
     * a un cliente determinado. No se almacena información personal del cliente.
     * También genera un recordatorio asociado, gestiona criterios repetidos,
     * asigna criterios a propiedades, crea historial de consultas y finalmente
     * envía una notificación por correo.
     *
     * @param  \Illuminate\Http\Request $request solicitud HTTP con criterios y propiedades del cliente
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON indicando el estado del proceso
     * @throws \Exception si ocurre un error durante la transacción o el procesamiento interno
     * @access public
     */
    public function guardarCriteriosYpropiedades(Request $request)
    {
        try {

            /* Genera un nuevo recordatorio relacionado a los criterios ingresados */
            $this->recordatorioController->storeDesdeClientesCriterio($request);
            /* La transacción se ejecuta utilizando la conexión mysql5 para alinearse con los modelos */
            DB::connection('mysql5')->transaction(function () use ($request) {

                /* Obtiene el ID del cliente desde el request */
                $idCliente = $request->id_cliente;

                /* Recupera criterios actualmente existentes para este cliente */
                $criteriosVentaEXISTENTES = CriterioBusquedaVentaService::getCriteriosExistentesPorIDCliente($idCliente);

                /* Arrays auxiliares para criterios nuevos y repetidos */
                $criteriosVentasNUEVOS     = [];
                $criteriosVentaREPTEIDOS   = [];

                /* Procesa criterios de venta enviados desde el request */
                $criteriosVenta = $request->input('criterios_venta', []);

                foreach ($criteriosVenta as $criterio) {

                    /* Asigna fecha por defecto al criterio de venta */
                    $criterio['fecha_criterio_venta'] = $criterio['fecha_criterio'] ?? now();

                    /* Validación de criterios repetidos */
                    foreach ($criteriosVentaEXISTENTES as $critExistente) {
                        if (
                            (string)$critExistente->id_tipo_inmueble   === (string)$criterio['id_tipo_inmueble'] &&
                            (string)$critExistente->cant_dormitorios  === (string)$criterio['cant_dormitorios']
                        ) {
                            $criteriosVentaREPTEIDOS[] = [
                                'id_criterio_venta' => $critExistente->id_criterio_venta,
                                'id_propiedad'      => $criterio['id_propiedad']
                            ];
                            /* Salta al siguiente criterio */
                            continue 2;
                        }
                    }

                    /* Guarda un nuevo criterio de venta */
                    $criterioVenta = $this->criterioBusquedaVentaService->guardarcriterioBusquedaVenta($criterio);

                    /* Registra el criterio nuevo junto a su propiedad vinculada (si corresponde) */
                    if (isset($criterio['id_propiedad'])) {
                        $criteriosVentasNUEVOS[] = [
                            'id_criterio_venta' => $criterioVenta->id_criterio_venta,
                            'id_propiedad'      => $criterio['id_propiedad']
                        ];
                    }
                }

                /* Procesa propiedades asignadas al cliente */
                $propiedadesVenta = $request->input('propiedades_venta', []);

                foreach ($propiedadesVenta as $propiedad) {

                    /* Asigna fecha por defecto a la consulta de propiedad */
                    $propiedad['fecha_consulta_propiedad'] = $propiedad['fecha_consulta'] ?? now();

                    /* Si la propiedad está relacionada con un criterio */
                    if (isset($propiedad['id_propiedad'])) {

                        /* Vincula propiedad con criterios nuevos */
                        foreach ($criteriosVentasNUEVOS as $criterioCreado) {
                            if ((string)$propiedad['id_propiedad'] === (string)$criterioCreado['id_propiedad']) {
                                $propiedad['id_criterio_venta']   = $criterioCreado['id_criterio_venta'];
                                $propiedad['estado_consulta_venta'] = "Activo";

                                /* Guarda historial de código de consulta */
                                $this->historialCodigoConsultaService
                                    ->guardarHistorialCodigoConsulta($propiedad['id_propiedad'], $criterioCreado['id_criterio_venta']);

                                break;
                            }
                        }

                        /* Vincula propiedad con criterios repetidos */
                        foreach ($criteriosVentaREPTEIDOS as $criterioRep) {
                            if ((string)$propiedad['id_propiedad'] === (string)$criterioRep['id_propiedad']) {
                                $propiedad['id_criterio_venta']   = $criterioRep['id_criterio_venta'];
                                $propiedad['estado_consulta_venta'] = "Activo";

                                $this->historialCodigoConsultaService
                                    ->guardarHistorialCodigoConsulta($propiedad['id_propiedad'], $criterioRep['id_criterio_venta']);

                                break;
                            }
                        }
                    }

                    /* Guarda la consulta de propiedad */
                    $this->consultaPropiedadVentaService->guardarConsultaPropVenta($propiedad);
                }

                /* Envía correo con criterios y propiedades procesadas */
                $this->envioMailService->enviar($criteriosVenta, $idCliente, $propiedadesVenta);
            });

            /* Éxito */
            return response()->json(['success' => true, 'message' => 'Cliente y criterios guardados correctamente']);
        } catch (\Exception $e) {

            /* Registro del error */
            log::error('Error en guardarCriteriosYpropiedades: ' . $e->getMessage());

            /* Respuesta de error */
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
