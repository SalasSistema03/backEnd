<?php

namespace App\Http\Controllers\clientes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\clientes\ClientesService;
use App\Services\clientes\CriterioBusquedaVentaService;
use App\Services\clientes\CriterioBusquedaAlquilerService;
use App\Services\clientes\TipoInmuebleService;
use App\Services\clientes\ZonasService;
use App\Services\clientes\UsuarioSectorService;
use App\Services\clientes\ConsultaPropVentaService;
use App\Services\clientes\ConsultaPropAlquilerService;
use App\Services\clientes\HistorialCodigoConsultaService;
use App\Services\clientes\Permisos;
use App\Services\clientes\EnvioMailService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\agenda\RecordatorioController;
use App\Models\cliente\ConsultaPropAlquiler;
use App\Models\cliente\CriterioBusquedaAlquiler;
use App\Models\cliente\CriterioBusquedaVenta;
use App\Models\usuarios_y_permisos\Usuario;
use App\Notifications\RecordatorioNotificacion;
use Carbon\Carbon;

class ClientesController extends Controller
{
    protected $clienteService;

    protected $tipoInmuebleService;
    protected $criterioBusquedaService;
    protected $criterioBusquedaVentaService;
    protected $criterioBusquedaAlquilerService;
    protected $consultaPropiedadVentaService;
    protected $consultaPropiedadAlquilerService;
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
        CriterioBusquedaAlquilerService $criterioBusquedaAlquiler,
        ZonasService $zona,
        UsuarioSectorService $usuarioSectorService,
        ConsultaPropVentaService $consultaPropiedadVentaService,
        ConsultaPropAlquilerService $consultaPropiedadAlquilerService,
        HistorialCodigoConsultaService $historialCodigoConsultaService,
        Permisos $permisoService,
        EnvioMailService $envioMailService,
        RecordatorioController $recordatorioController
    ) {
        $this->clienteService = $clientes;
        $this->tipoInmuebleService = $tipoInmueble;
        $this->criterioBusquedaVentaService = $criterioBusquedaVenta;
        $this->criterioBusquedaAlquilerService = $criterioBusquedaAlquiler;
        $this->zonaService = $zona;
        $this->usuarioSectorService = $usuarioSectorService;
        $this->consultaPropiedadVentaService = $consultaPropiedadVentaService;
        $this->consultaPropiedadAlquilerService = $consultaPropiedadAlquilerService;
        $this->historialCodigoConsultaService = $historialCodigoConsultaService;
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        /* $this->usuario = Usuario::find($this->usuario_id); */
        $this->permisoService = $permisoService;
        $this->envioMailService = $envioMailService;
        $this->recordatorioController = $recordatorioController;
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
        Log::info('informacion de request', $request->all());
        //dd('hola');


        try {
            // Validación cuando el sector es alquiler
            $clienteData = $request->input('cliente', []);
            if (isset($clienteData['sector_asesor']) && $clienteData['sector_asesor'] === 'alquiler') {
                $validator = Validator::make($request->all(), [
                    'cliente.telefono' => 'required',
                    'cliente.nombre' => 'required',
                    'cliente.id_asesor_alquiler' => 'required',
                    'cliente.ingreso_alq' => 'required',
                    'cliente.usuario_id' => 'required',
                    'propiedades_alquiler' => 'required_without:criterios_alquiler|array',
                    'criterios_alquiler' => 'required_without:propiedades_alquiler|array',
                ], [
                    'cliente.telefono.required' => 'El teléfono es obligatorio.',
                    'cliente.nombre.required' => 'El nombre es obligatorio.',
                    'cliente.id_asesor_alquiler.required' => 'El asesor de alquiler es obligatorio.',
                    'cliente.ingreso_alq.required' => 'El canal de ingreso es obligatorio.',
                    'cliente.usuario_id.required' => 'El usuario es obligatorio.',
                    'propiedades_alquiler.required_without' => 'Debe ingresar al menos una propiedad o un criterio de alquiler.',
                    'criterios_alquiler.required_without' => 'Debe ingresar al menos una propiedad o un criterio de alquiler.',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors()
                    ], 422);
                }
            }

            $cliente = null;
            $criteriosVentaCreados = [];
            $criteriosAlquilerCreados = [];
            $propiedadesVentaInput = $request->input('propiedades_venta', []);
            $propiedadesAlquilerInput = $request->input('propiedades_alquiler', []);
            $usuarioId =   auth('api')->id();
            //Log::info('Esto es informacion de request', $request->all());
            //Log::info('Esto es informacion de propiedadesventainput', $propiedadesVentaInput);
            //Logica relacionada con los recordatorios
            //$this->recordatorioController->storeDesdeClientes($request);
            //dd('hola');

            DB::connection('mysql5')->transaction(function () use ($request, &$cliente, &$criteriosVentaCreados, &$criteriosAlquilerCreados, $propiedadesVentaInput, $propiedadesAlquilerInput, $usuarioId) {
                // 1. GUARDAR EL CLIENTE
                $clienteData = $request->input('cliente');
                if ($clienteData['sector_asesor'] === 'venta') {
                    $clienteData['id_asesor_venta'] = $clienteData['id_asesor'] ?? null;
                } else {
                    $clienteData['id_asesor_alquiler'] = $clienteData['id_asesor_alquiler'] ?? null;
                }


                //Log::info('antes de guardar cliente');
                $cliente = $this->clienteService->guardarcliente($clienteData);
                // 2. GUARDAR O SINCRONIZAR CRITERIOS DE VENTA
                $criteriosVenta = $request->input('criterios_venta', []);
                $criteriosAlquiler = $request->input('criterios_alquiler', []);
                //log::info('Criterios de venta: ' . json_encode($criteriosVenta));
                //log::info('Criterios de alquiler: ' . json_encode($criteriosAlquiler));
                //dd('hola');
                if ($clienteData['sector_asesor'] === 'venta') {
                    // Cliente nuevo: agregar solo criterios nuevos (sin id_criterio_venta)
                    foreach ($criteriosVenta as $criterio) {
                        // Si tiene id_criterio_venta, es un criterio existente, lo saltamos
                        if (isset($criterio['id_criterio_venta'])) {
                            //Log::info('Omitiendo criterio existente', ['id_criterio_venta' => $criterio['id_criterio_venta']]);
                            continue;
                        }

                        $criterio['id_cliente'] = $cliente->id_cliente;
                        $criterio['usuario_id'] = $cliente->usuario_id;
                        $criterio['fecha_criterio_venta'] = $criterio['fecha_criterio'] ?? now();
                        $criterioVenta = $this->criterioBusquedaVentaService->guardarcriterioBusquedaVenta($criterio);
                        // Almacenar el criterio creado con el ID real de la base de datos
                        if (isset($criterio['id_propiedad'])) {
                            $criteriosVentaCreados[] = [
                                'id_criterio_venta' => $criterioVenta->id_criterio_venta,
                                'id_tipo_inmueble'  => $criterio['id_tipo_inmueble'],
                                'cant_dormitorios'  => $criterio['cant_dormitorios'],
                                'id_propiedad'      => $criterio['id_propiedad']
                            ];
                        }
                    }


                    // 4. GUARDAR EL HISTORIAL DE CONSULTAS (DESPUÉS de que todo lo demás está creado)

                    foreach ($propiedadesVentaInput as $propiedad) {

                        if (isset($propiedad['id_con_prop_venta'])) {
                            // Log::info('Omitiendo propiedad existente', ['id_con_prop_venta' => $propiedad['id_con_prop_venta']]);
                            continue;
                        }
                        $propiedad['id_cliente'] = $cliente->id_cliente;
                        $propiedad['usuario_id'] = $cliente->usuario_id;
                        $propiedad['fecha_consulta_propiedad'] = $propiedad['fecha_consulta'] ?? now();
                        $propiedad['estado_consulta_venta'] = "Activo";


                        if (!isset($propiedad['id_propiedad'])) continue;

                        $encontrado = false;

                        //Log::info('antes de entrar al for', $criteriosVentaCreados);
                        foreach ($criteriosVentaCreados as $criterioCreado) {

                            if ($propiedad['id_tipo_inmueble'] == $criterioCreado['id_tipo_inmueble'] && $propiedad['cant_dormitorios'] == $criterioCreado['cant_dormitorios']) {

                                $propiedad['id_criterio_venta'] = $criterioCreado['id_criterio_venta'];

                                $this->consultaPropiedadVentaService->guardarConsultaPropVenta($propiedad);

                                $this->historialCodigoConsultaService
                                    ->guardarHistorialCodigoConsulta($propiedad['id_propiedad'], $criterioCreado['id_criterio_venta']);

                                $encontrado = true;
                                break;
                            }
                        }
                        //Log::info('Salio de criteriosventacrados');
                        // Solo si ningún criterio coincidió
                        if (!$encontrado) {
                            unset($propiedad['id_tipo_inmueble'], $propiedad['cant_dormitorios']);
                            $this->consultaPropiedadVentaService->guardarConsultaPropVenta($propiedad);
                        }
                    }

                    try {
                        // Encontrar el criterioventa con el ID más grande
                        $criterioVentaMasGrande = null;
                        $maxId = 0;

                        foreach ($criteriosVentaCreados as $criterio) {
                            if (isset($criterio['id_criterio_venta']) && $criterio['id_criterio_venta'] > $maxId) {
                                $maxId = $criterio['id_criterio_venta'];
                                $criterioVentaMasGrande = $criterio;
                            }
                        }

                        // Si no hay criterios creados, usar el primero del log si existe
                        if ($criterioVentaMasGrande === null && !empty($criteriosVenta)) {
                            foreach ($criteriosVenta as $criterio) {
                                if (isset($criterio['id_criterio_venta']) && $criterio['id_criterio_venta'] > $maxId) {
                                    $maxId = $criterio['id_criterio_venta'];
                                    $criterioVentaMasGrande = $criterio;
                                }
                            }
                        }

                        $idCriterioVenta = $criterioVentaMasGrande ? $criterioVentaMasGrande['id_criterio_venta'] : null;

                        $mensaje = [
                            'descripcion'       => $cliente->nombre . ' ' . $cliente->apellido,
                            'fecha'             => now()->isoFormat('DD/MM/YYYY'),  // 13/04/2026
                            'hora'              => now()->isoFormat('HH:mm'),       // 14:53
                            'activo'            => 1,
                            'usuarioNotificar'  => $request->input('cliente.id_asesor'),
                            'cliente_id'        => $cliente->id_cliente,
                            'id_criterio_venta' => $idCriterioVenta,
                            'pertenece'         => "asesores",
                            'folio'             => "-"
                        ];
                    } catch (\Exception $e) {
                        Log::error('Error al crear mensaje', ['error' => $e->getMessage()]);
                    }




                    $usuario = Usuario::find($usuarioId);
                    //Log::info('antes de entrar al if de usuaiuo', ['usuarioId' => $usuarioId, 'usuario' => $usuario]);
                    if ($usuario) {

                        $usuario->notify(new RecordatorioNotificacion($mensaje));
                    }


                    //no borrar este comentado
                    $this->envioMailService->enviarNuevoMail($criteriosVenta, $cliente->id_cliente, $propiedadesVentaInput, 'venta');
                } else {
                    // Cliente nuevo: agregar solo criterios nuevos (sin id_criterio_venta)
                    foreach ($criteriosAlquiler as $criterio) {
                        // Si tiene id_criterio_venta, es un criterio existente, lo saltamos
                        if (isset($criterio['id_criterio_alquiler'])) {
                            Log::info('Omitiendo criterio existente', ['id_criterio_alquiler' => $criterio['id_criterio_alquiler']]);
                            continue;
                        }

                        $criterio['id_cliente'] = $cliente->id_cliente;
                        $criterio['usuario_id'] = $cliente->usuario_id;
                        $criterio['fecha_criterio_alquiler'] = $criterio['fecha_criterio'] ?? now();
                        //$criterioVenta = $this->criterioBusquedaVentaService->guardarcriterioBusquedaVenta($criterio);
                        $criterioAlquiler = $this->criterioBusquedaAlquilerService->guardarcriterioBusquedaAlquiler($criterio);
                        //dd('hola');
                        // Almacenar el criterio creado con el ID real de la base de datos
                        if (isset($criterio['id_propiedad'])) {
                            $criteriosAlquilerCreados[] = [
                                'id_criterio_alquiler' => $criterioAlquiler->id_criterio_alquiler,
                                'id_tipo_inmueble'  => $criterio['id_tipo_inmueble'],
                                'cant_dormitorios'  => $criterio['cant_dormitorios'],
                                'id_propiedad'      => $criterio['id_propiedad']
                            ];
                        }
                    }

                    // 4. GUARDAR EL HISTORIAL DE CONSULTAS (DESPUÉS de que todo lo demás está creado)
                    //Log::info('propiedadesAlquilerInput', $propiedadesAlquilerInput);

                    if (!empty($propiedadesAlquilerInput)) {
                        foreach ($propiedadesAlquilerInput as $propiedad) {

                            if (isset($propiedad['id_con_prop_alquiler'])) {
                                Log::info('Omitiendo propiedad existente', ['id_con_prop_venta' => $propiedad['id_con_prop_venta']]);
                                continue;
                            }
                            $propiedad['id_cliente'] = $cliente->id_cliente;
                            $propiedad['usuario_id'] = $cliente->usuario_id;
                            $propiedad['fecha_consulta_propiedad'] = $propiedad['fecha_consulta'] ?? now();
                            $propiedad['estado_consulta_venta'] = "Activo";



                            if (!isset($propiedad['id_propiedad'])) continue;

                            $encontrado = false;

                            //Log::info('antes de entrar al for', $criteriosAlquilerCreados);

                            //dd('hola');
                            foreach ($criteriosAlquilerCreados as $criterioCreado) {

                                if ($propiedad['id_tipo_inmueble'] == $criterioCreado['id_tipo_inmueble'] && $propiedad['cant_dormitorios'] == $criterioCreado['cant_dormitorios']) {

                                    $propiedad['id_criterio_alquiler'] = $criterioCreado['id_criterio_alquiler'];

                                    $this->consultaPropiedadAlquilerService->guardarConsultaPropAlquiler($propiedad);

                                    /* $this->historialCodigoConsultaService
                                        ->guardarHistorialCodigoConsulta($propiedad['id_propiedad'], $criterioCreado['id_criterio_alquiler']); */

                                    $encontrado = true;
                                    break;
                                }
                            }
                            //Log::info('Salio de criteriosventacrados');
                            // Solo si ningún criterio coincidió
                            if (!$encontrado) {
                                unset($propiedad['id_tipo_inmueble'], $propiedad['cant_dormitorios']);
                                $this->consultaPropiedadAlquilerService->guardarConsultaPropAlquiler($propiedad);
                            }
                        }
                    }



                    try {
                        // Encontrar el criterio alquiler con el ID más grande
                        $criterioAlquilerMasGrande = null;
                        $maxId = 0;

                        foreach ($criteriosAlquilerCreados as $criterio) {
                            if (isset($criterio['id_criterio_alquiler']) && $criterio['id_criterio_alquiler'] > $maxId) {
                                $maxId = $criterio['id_criterio_alquiler'];
                                $criterioAlquilerMasGrande = $criterio;
                            }
                        }

                        // Si no hay criterios creados, usar el primero del log si existe
                        if ($criterioAlquilerMasGrande === null && !empty($criteriosAlquiler)) {
                            foreach ($criteriosAlquiler as $criterio) {
                                if (isset($criterio['id_criterio_alquiler']) && $criterio['id_criterio_alquiler'] > $maxId) {
                                    $maxId = $criterio['id_criterio_alquiler'];
                                    $criterioAlquilerMasGrande = $criterio;
                                }
                            }
                        }

                        $idCriterioAlquiler = $criterioAlquilerMasGrande ? $criterioAlquilerMasGrande['id_criterio_alquiler'] : null;

                        $mensaje = [
                            'descripcion'       => $cliente->nombre . ' ' . $cliente->apellido,
                            'fecha'             => now()->isoFormat('DD/MM/YYYY'),  // 13/04/2026
                            'hora'              => now()->isoFormat('HH:mm'),       // 14:53
                            'activo'            => 1,
                            'usuarioNotificar'  => $request->input('cliente.id_asesor'),
                            'cliente_id'        => $cliente->id_cliente,
                            'id_criterio_alquiler' => $idCriterioAlquiler,
                            'pertenece'         => "asesores",
                            'folio'             => "-"
                        ];
                    } catch (\Exception $e) {
                        Log::error('Error al crear mensaje', ['error' => $e->getMessage()]);
                    }




                    $usuario = Usuario::find($usuarioId);
                    //Log::info('antes de entrar al if de usuaiuo', ['usuarioId' => $usuarioId, 'usuario' => $usuario]);
                    /* if ($usuario) {

                        $usuario->notify(new RecordatorioNotificacion($mensaje));
                    } */


                    //no borrar este comentado
                    $this->envioMailService->enviarNuevoMail($criteriosAlquiler, $cliente->id_cliente, $propiedadesAlquilerInput, 'alquiler');
                }


                //Log::info('sssssssssssssssssssssssss');
                //Log::info('criteriosVenta', $criteriosVenta);
                //mensajes

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
        //Log::info($telefono);
        if (!$telefono) {
            return response()->json(['error' => 'Teléfono requerido'], 400);
        }

        $cliente = $this->clienteService->clientePorTelefonoService($telefono);

        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        return response()->json(['cliente' => $cliente]);
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
            //$this->recordatorioController->storeDesdeClientesCriterio($request);
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

    public function getCantidadClientes()
    {
        $conteoPorAsesor = DB::connection('mysql5')
            ->table('criterio_busqueda_venta as cv')
            ->join('clientes as c', 'c.id_cliente', '=', 'cv.id_cliente')
            ->whereDate('cv.fecha_criterio_venta', Carbon::today())
            ->select(
                'c.id_asesor_venta',
                DB::raw('COUNT(*) as cantidad')
            )
            ->whereNotNull('c.id_asesor_venta')
            ->groupBy('c.id_asesor_venta')
            ->get();
        //Log::info($conteoPorAsesor);
        return $conteoPorAsesor;
    }

    public function traerClientesAsignados()
    {
        $hoy = Carbon::today();
        $usuarioId = 36;
        // 1. Obtener consultas de propiedades de hoy
        $consultas = ConsultaPropAlquiler::where('usuario_id', $usuarioId)
            ->where('fecha_consulta_propiedad', '>=', $hoy)
            ->with([
                'cliente',
                'cliente.asesor_alquiler.usuario'
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'consulta_' . $item->id_con_prop_alquiler,
                    'id_cliente' => $item->id_cliente,
                    'tipo' => 'propiedad',
                    'fecha' => $item->fecha_consulta_propiedad,
                    'fecha_consulta_propiedad' => $item->fecha_consulta_propiedad, // Para mantener compatibilidad con tu frontend
                    'cliente' => $item->cliente
                ];
            });
        // 2. Obtener criterios de búsqueda de hoy
        $criterios = CriterioBusquedaAlquiler::where('usuario_id', $usuarioId)
            ->where('fecha_criterio_alquiler', '>=', $hoy)
            ->with([
                'cliente',
                'cliente.asesor_alquiler.usuario'
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'criterio_' . $item->id_criterio,
                    'id_cliente' => $item->id_cliente,
                    'tipo' => 'criterio',
                    'fecha' => $item->fecha_criterio_alquiler,
                    'fecha_consulta_propiedad' => $item->fecha_criterio_alquiler, // Para mantener compatibilidad con tu frontend
                    'cliente' => $item->cliente
                ];
            });
        // 3. Unir ambas listas, ordenar por fecha descendente y si deseas evitar clientes duplicados usar unique('id_cliente')
        $resultado = $consultas->concat($criterios)
            ->unique('id_cliente') // Si solo quieres mostrar 1 fila por cliente asignado
            ->sortByDesc('fecha')
            ->values();
        return response()->json($resultado);
    }
}
