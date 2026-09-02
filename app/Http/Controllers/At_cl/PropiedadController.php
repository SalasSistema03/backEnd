<?php

namespace App\Http\Controllers\At_cl;

use App\Models\At_cl\Calle;
use App\Models\At_cl\Foto;
use App\Models\At_cl\Observaciones_propiedades;
use App\Models\At_cl\Propiedad;
use App\Models\impuesto\Agua_padron;
use App\Models\impuesto\Agua_carga;
use App\Models\impuesto\Api_carga;
use App\Models\impuesto\Api_padron;
use App\Models\impuesto\Exp_broche;
use App\Models\impuesto\Exp_Unidades;
use App\Models\impuesto\Exp_unidades_sys;
use App\Models\impuesto\Gas_carga;
use App\Models\impuesto\Gas_padron;
use App\Models\impuesto\Tgi_carga;

use App\Models\impuesto\Tgi_padron;
use App\Models\usuarios_y_permisos\Usuario;
use App\Services\At_cl\EmpresaPropiedadService;
use App\Services\At_cl\FiltroPropiedadService;
use App\Services\At_cl\PrecioService;
use App\Services\At_cl\Propiedades_padronService;
use App\Services\At_cl\PropiedadMediaService;
use App\Services\At_cl\PropiedadService;
use App\Services\At_cl\TasacionService;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use App\Services\contable\sellado\RegistroSelladoService;
use App\Support\At_cl\PropertyUpdateMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


/**
 * Controlador encargado de gestionar la búsqueda y CRUD completo de las propiedades,
 * incluyendo filtrado avanzado, ordenamiento dinámico,restricciones de acceso
 * según permisos del usuario y modificacion de atributos de las propiedades.
 *
 * Este controlador coordina múltiples servicios relacionados con precios,
 * observaciones, fotografías, videos, documentación, tasaciones y padrones.
 */
class PropiedadController
{
    protected $propiedad_padronService;

    protected $propiedadService;

    protected $empresaPropiedadService;

    /**
     * Constructor del controlador - Inicializa servicios necesarios
     *
     * @param  PropiedadService  $propiedadService  Servicio de gestión de propiedades
     * @param  Propiedades_padronService  $propiedad_padronService  Servicio de padrón de propiedades
     * @param  EmpresaPropiedadService  $empresaPropiedadService  Servicio de empresas de propiedades
     */
    public function __construct(
        PropiedadService $propiedadService,
        Propiedades_padronService $propiedad_padronService,
        EmpresaPropiedadService $empresaPropiedadService,
        protected RegistroSelladoService $registro_sellado,
    ) {
        // Inicializar servicios utilizados
        $this->propiedadService = $propiedadService;
        $this->propiedad_padronService = $propiedad_padronService;
        $this->empresaPropiedadService = $empresaPropiedadService;
    }

    /**
     * Guarda una nueva propiedad en el sistema con todos sus datos relacionados
     *
     * Este método maneja la creación completa de una propiedad incluyendo:
     * - Datos básicos de la propiedad
     * - Información de venta y alquiler
     * - Comodidades y descripción
     * - Archivos multimedia (fotos, videos, documentos)
     * - Asociación con propietarios
     * - Vinculación con empresas (folios)
     *
     * @param  Request  $request  Datos del formulario
     * @param  int  $id  ID del usuario que crea la propiedad
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado
     */
    public function guardarPropiedad(Request $request, $id)
    {
        // Limpiar y decodificar datos JSON del request
        $comodidades = $this->cleanArray(json_decode($request->comodidades, true) ?? []);
        $descripcion = $this->cleanArray(json_decode($request->descripcion, true) ?? []);
        $venta = $this->cleanArray(json_decode($request->venta, true) ?? []);
        $alquiler = $this->cleanArray(json_decode($request->alquiler, true) ?? []);
        $condicionAlquiler = $this->cleanArray(json_decode($request->condicion_alquiler, true) ?? []);
        $propietario = $this->cleanArray(json_decode($request->propietario, true) ?? []);
        $novedades = $this->cleanArray(json_decode($request->novedades, true) ?? []);

        // Validaciones básicas para el guardado de la propiedad
        $validator = Validator::make(
            [
                'cod_venta' => $venta['cod_venta'] ?? null,
                'cod_alquiler' => $alquiler['cod_alquiler'] ?? null,
                'calle_id' => $request->calle_id,
                'numero_calle' => $request->altura,
                'piso' => $request->piso,
                'departamento' => $request->dto,
                'llave' => $request->llave,
                'dormitorios' => $comodidades['dormitorios'] ?? null,
                'banios' => $comodidades['banios'] ?? null,
                'lotes' => $comodidades['lotes'] ?? null,
                'lote_cubierto' => $comodidades['lote_cubierto'] ?? null,
                'numero_cochera' => $comodidades['numero_cochera'] ?? null,
                'monto_venta' => $venta['monto_venta'] ?? null,
                'folio_central' => $alquiler['FCentral'] ?? null,
                'folio_candioti' => $alquiler['FCandioti'] ?? null,
                'folio_tribunales' => $alquiler['FTribunales'] ?? null,
                'venta_fecha_alta' => $venta['venta_fecha_alta'] ?? null,
                'fecha_autorizacion_venta' => $venta['fecha_autorizacion_venta'] ?? null,
                'alquiler_fecha_alta' => $alquiler['alquiler_fecha_alta'] ?? null,
                'fecha_autorizacion_alquiler' => $alquiler['fecha_autorizacion_alquiler'] ?? null,
            ],
            [
                'cod_venta' => ['nullable', 'required_without:cod_alquiler', 'unique:propiedades,cod_venta'],
                'cod_alquiler' => ['nullable', 'required_without:cod_venta', 'unique:propiedades,cod_alquiler'],
                'calle_id' => 'exists:calle,id',
                'numero_calle' => ['nullable', 'regex:/^[0-9]+$/', 'digits_between:1,11'],
                'piso' => ['nullable', 'regex:/^[0-9]+$/', 'digits_between:1,11'],
                'departamento' => ['nullable', 'regex:/^[A-Za-z0-9]+$/'],
                'llave' => ['nullable', 'digits_between:1,11'],
                'dormitorios' => ['nullable', 'digits_between:1,11'],
                'banios' => ['nullable', 'digits_between:1,11'],
                'lotes' => ['nullable', 'digits_between:1,11'],
                'lote_cubierto' => ['nullable', 'digits_between:1,11'],
                'numero_cochera' => ['nullable', 'digits_between:1,11'],
                'monto_venta' => ['nullable', 'numeric', 'min:0'],
                'folio_central' => ['nullable', 'regex:/^[0-9]+$/'],
                'folio_candioti' => ['nullable', 'regex:/^[0-9]+$/'],
                'folio_tribunales' => ['nullable', 'regex:/^[0-9]+$/'],
                'venta_fecha_alta' => ['nullable', 'date_format:Y-m-d'],
                'fecha_autorizacion_venta' => ['nullable', 'date_format:Y-m-d'],
                'alquiler_fecha_alta' => ['nullable', 'date_format:Y-m-d'],
                'fecha_autorizacion_alquiler' => ['nullable', 'date_format:Y-m-d'],
            ],
            [
                'cod_venta.required_without' => 'Debe ingresar un código de venta o de alquiler.',
                'cod_alquiler.required_without' => 'Debe ingresar un código de venta o de alquiler.',
                'cod_venta.unique' => 'El código de venta ya se encuentra en uso.',
                'cod_alquiler.unique' => 'El código de alquiler ya se encuentra en uso.',
                'calle_id.exists' => 'La calle seleccionada no existe.',
                'numero_calle.regex' => 'El número de la calle no puede contener decimales.',
                'numero_calle.digits_between' => 'El número de calle debe tener entre 1 y 11 dígitos.',
                'piso.regex' => 'El piso no puede contener decimales.',
                'piso.digits_between' => 'El piso debe tener entre 1 y 11 dígitos.',
                'departamento.regex' => 'El departamento no puede contener caracteres especiales.',
                'llave.digits_between' => 'La llave debe tener entre 1 y 11 dígitos.',
                'dormitorios.digits_between' => 'Los dormitorios deben tener entre 1 y 11 dígitos.',
                'banios.digits_between' => 'Los baños deben tener entre 1 y 11 dígitos.',
                'lotes.digits_between' => 'Los lotes deben tener entre 1 y 11 dígitos.',
                'lote_cubierto.digits_between' => 'El lote cubierto debe tener entre 1 y 11 dígitos.',
                'numero_cochera.digits_between' => 'El número de cochera debe tener entre 1 y 11 dígitos.',
                'monto_venta.numeric' => 'El monto de venta debe ser un número válido.',
                'monto_venta.min' => 'El monto de venta debe ser mayor o igual a 0.',
                'folio_central.regex' => 'El folio de Central debe ser un número entero.',
                'folio_candioti.regex' => 'El folio de Candioti debe ser un número entero.',
                'folio_tribunales.regex' => 'El folio de Tribunales debe ser un número entero.',
                'venta_fecha_alta.date_format' => 'La fecha de alta de venta no es una fecha valida.',
                'fecha_autorizacion_venta.date_format' => 'La fecha de autorización de venta no es una fecha valida.',
                'alquiler_fecha_alta.date_format' => 'La fecha de alta de alquiler no es una fecha valida.',
                'fecha_autorizacion_alquiler.date_format' => 'La fecha de autorización de alquiler no es una fecha valida.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Preparar datos para el servicio
            $datos = [
                'calle_id' => $request->calle_id,
                'altura' => $request->altura,
                'ph' => $request->ph,
                'piso' => $request->piso,
                'dto' => $request->dto,
                'inmueble_id' => $request->inmueble_id,
                'zona_id' => $request->zona_id,
                'provincia_id' => $request->provincia_id,
                'llave' => $request->llave,
                'comentario_llave' => $request->observaciones_llaves,
                'cartel' => $request->cartel,
                'observaciones_cartel' => $request->observaciones_cartel,
                'comodidades' => $comodidades,
                'descripcion' => $descripcion,
                'venta' => $venta,
                'alquiler' => $alquiler,
                'condicionAlquiler' => $condicionAlquiler,
            ];

            // Crear la propiedad usando el servicio
            $propiedad_creada = (new PropiedadService)->crearPropiedad($datos, $id);

            // Log::info('Propiedad creada: ' . $propiedad_creada->id);
            // Log::info('DB_DATABASE: ' . config('database.connections.mysql.database'));
            // Log::info('DB_HOST: ' . config('database.connections.mysql.host'));
            // Crear tasación si hay datos de venta
            (new TasacionService)->crearDesdeRequest($venta, $propiedad_creada->id);

            // Crear registro de precios
            (new PrecioService)->crearDesdeRequest($venta, $alquiler, $propiedad_creada->id);
            try {
                // Carga de archivos multimedia
                (new PropiedadMediaService)->subirDesdeRequest($request, $propiedad_creada->id);
            } catch (\Exception $e) {
                Log::error('Fallo multimedia: ' . $e->getMessage());
            }
            // Asociación de la propiedad a empresas (folios)
            $folios = [
                1 => $alquiler['FCentral'] ?? null,
                2 => $alquiler['FCandioti'] ?? null,
                3 => $alquiler['FTribunales'] ?? null,
            ];

            if (($alquiler['FCentral'] ?? null) != null || ($alquiler['FCandioti'] ?? null) != null || ($alquiler['FTribunales'] ?? null) != null) {
                (new EmpresaPropiedadService)->asociarNuevoFolio([$folios], $propiedad_creada->id);
            }

            // Asociación de la propiedad con los propietarios
            if (! empty($propietario)) {
                $propietario_decoded = is_array($propietario) ? $propietario : json_decode($propietario, true);

                if ($propietario_decoded) {
                    foreach ($propietario_decoded as $propietario_item) {
                        if (isset($propietario_item['id'])) {
                            $this->propiedad_padronService->vincularActualizacion($propiedad_creada->id, [$propietario_item]);
                        }
                    }
                }
            }

            //Log::info('Contante de filas: ' . DB::table('propiedades')->count());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad guardada correctamente.',
                'data' => [
                    'id' => $propiedad_creada->id,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            //Log::error("ERROR FATAL: " . $e->getMessage()); // Revisa el log de laravel después de esto
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Solo para desarrollo
            ], 500);
        }
    }

    /**
     * Busca propiedades según filtros avanzados
     *
     * Este método aplica filtros múltiples y ordenamiento para encontrar propiedades
     * que coincidan con los criterios de búsqueda especificados.
     *
     * @param  Request  $request  Parámetros de filtrado y búsqueda
     * @param  FiltroPropiedadService  $filtroService  Servicio de filtrado
     * @return \Illuminate\Http\JsonResponse Lista de propiedades filtradas
     */
    public function buscaPropiedad(Request $request, FiltroPropiedadService $filtroService)
    {
        // Preparar filtros para el servicio
        $filtros = [
            'busqueda' => $request->busqueda,
            'codigo' => $request->codigo,
            'calle_id' => $request->calle_id,
            'inmuebles' => $request->inmuebles,
            'zonas' => $request->zonas,
            'cochera' => $request->cochera,
            'mascotas' => $request->mascotas,
            'habitaciones' => $request->habitaciones,
            'desde' => $request->desde,
            'hasta' => $request->hasta,
            'orden' => $request->orden,
            'oferta' => $request->busqueda, // Para que el servicio sepa si es venta o alquiler
            'tipo_inmueble' => $request->inmuebles,
            'ampliar' => $request->ampliar,
        ];

        // El servicio se encarga de todo: filtrado + ordenamiento
        $propiedades = $filtroService->filtrarPropiedades($filtros);

        // Cargar relaciones necesarias para la respuesta
        foreach ($propiedades as $propiedad) {
            $propiedad->calle->name ?? null;
            $propiedad->zona->name ?? null;
            $propiedad->tipoInmueble->inmueble ?? null;
            $propiedad->precioActual ?? null;
        }

        // Formatear resultado para la respuesta
        $resultado = $propiedades->map(function ($propiedad) {
            return [
                'id' => $propiedad->id,
                'cod_venta' => $propiedad->cod_venta,
                'cod_alquiler' => $propiedad->cod_alquiler,
                'calle' => $propiedad->calle?->name,
                'numero_calle' => $propiedad->numero_calle,
                'zona' => $propiedad->zona?->name,
                'tipo' => $propiedad->tipoInmueble?->inmueble,
                'cantidad_dormitorios' => $propiedad->cantidad_dormitorios,
                'banios' => $propiedad->banios,
                'cochera' => $propiedad->cochera,
                'mascota' => $propiedad->mascota,
                /* 'moneda_precio' => $propiedad->precioActual?->moneda, */
                'precio_alquiler_pesos' => $propiedad->precioActual?->moneda_alquiler_pesos ?? null,
                'precio_alquiler_dolar' => $propiedad->precioActual?->moneda_alquiler_dolar ?? null,
                'precio_venta_dolar' => $propiedad->precioActual?->moneda_venta_dolar ?? null,
                'precio_venta_pesos' => $propiedad->precioActual?->moneda_venta_pesos ?? null,
                'estado_alquiler' => $propiedad->estadoAlquiler?->name,
                'estado_venta' => $propiedad->estadoVenta?->name,
                'piso' => $propiedad->piso,
                'departamento' => $propiedad->departamento,
                'folio' => $propiedad->folios,
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Muestra los detalles completos de una propiedad específica
     *
     * Este método carga todas las relaciones necesarias de una propiedad
     * incluyendo fotos, videos, documentación, propietarios, contratos, etc.
     *
     * @param  Request  $request  Contiene el ID de la propiedad a mostrar
     * @return \Illuminate\Http\JsonResponse Datos completos de la propiedad
     */
    public function MuestraPropiedad(Request $request)
    {
        try {
            // Cargar propiedad con todas sus relaciones
            $propiedad = Propiedad::with([
                'calle',
                'zona',
                'tipoInmueble',
                'precioActual',
                'provincia',
                'estadoGeneral',
                'estadoAlquiler',
                'estadoVenta',
                'tasaciones',
                'usuarioAsesor',
                'usuarioCaptadorInt',
                'usuarioCaptadorIntV',
                'usuarioCaptadorIntA',
                'folios',
                'fotos',
                'video',
                'documentacion',
                'propietarios',
                'observacionesPropiedades',
                'historialEstadosAlquiler',
                'historialEstadosVenta',
            ])->find($request->id);

            if (!$propiedad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propiedad no encontrada',
                ], 404);
            }

            // Procesar impuestos para cada folio
            $impuestosData = $this->procesarImpuestosPorFolios($propiedad->folios);

            // Agregar datos de impuestos a la propiedad
            $propiedad->impuestos = $impuestosData;

            // Procesar Expensas por cada Folio
            if ($propiedad->folios) {
                foreach ($propiedad->folios as $folio) {
                    $folio->montoExpensa = null; // Inicializar por defecto

                    if (!empty($folio->folio)) {
                        $idCasaExpensas = Exp_unidades_sys::where('folio', $folio->folio)->first();

                        if ($idCasaExpensas && !empty($idCasaExpensas->casa)) {
                            $expensasUnidades = Exp_Unidades::where('id_casa', $idCasaExpensas->casa)->first();

                            if ($expensasUnidades && !empty($expensasUnidades->id)) {
                                $expensa = Exp_broche::with('exp_administrador_consorcio')
                                    ->where('unidad', $expensasUnidades->id)
                                    ->orderBy('vencimiento', 'desc')
                                    ->first();

                                if ($expensa) {
                                    $expensa->tipo = 'EXPENSAS';
                                    $expensa->nombre_administrador = $expensa->exp_administrador_consorcio->nombre ?? null;
                                    $folio->montoExpensa = $expensa;
                                }
                            }
                        }
                    }
                }
            }

            // Obtener información adicional de contratos y folios
            $foliosActivos = $propiedad->buscarCasa();
            $contratoMasReciente = $propiedad->buscarContratoMasReciente();

            // Obtener el detalle del contrato más alto
            $detalleContrato = null;
            if (!empty($contratoMasReciente) && isset($contratoMasReciente['id_contrato_cabecera'])) {
                $detalleContrato = $propiedad->buscarDetalleContratoMasAlto($contratoMasReciente['id_contrato_cabecera']);
            }

            // Convertir a array y agregar información adicional
            $propiedadArray = $propiedad->toArray();
            if ($propiedad->folios) {
                $propiedadArray['folios'] = $propiedad->folios->toArray();
            }
            $propiedadArray['buscarFolioActivo'] = $foliosActivos;
            $propiedadArray['buscarContratoMasReciente'] = $contratoMasReciente;
            $propiedadArray['detalleContrato'] = $detalleContrato;
            $propiedadArray['impuestos'] = $impuestosData;

            // Eliminar valores null para limpiar la respuesta
            $propiedadFiltrada = array_filter($propiedadArray, function ($value) {
                return $value !== null;
            });

            // Permisos de botones
            $usuario_id = auth('api')->id();
            $accessService = new PermitirAccesoSelladoService($usuario_id);
            $botones = [
                'propietario' => $accessService->tieneAcceso('propietario'),
                'informacion_venta' => $accessService->tieneAcceso('informacion_venta'),
                'informacion_alquiler' => $accessService->tieneAcceso('informacion_alquiler'),
                'modificar' => $accessService->tieneAcceso('modificar'),
            ];
            //Log::info('Propiedad filtrada: ' . json_encode($propiedadFiltrada));

            return response()->json([
                'success' => true,
                'data' => $propiedadFiltrada,
                'botones' => $botones,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos de la propiedad',
            ], 500);
        }
    }


    /**
     * Actualiza los datos de una propiedad existente
     *
     * Este método maneja la actualización completa de una propiedad incluyendo:
     * - Datos básicos y comodidades
     * - Información de venta y alquiler
     * - Archivos multimedia (fotos, videos, documentos)
     * - Propietarios asociados
     * - Estados y historial
     *
     * @param  Request  $request  Datos actualizados de la propiedad
     * @return \Illuminate\Http\JsonResponse Resultado de la operación
     */
    public function actualizarPropiedad(Request $request)
    {
        //Log::info('entro', [$request->all()]);

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:propiedades,id'],
            'comodidades' => ['nullable', 'json'],
            'descripcion' => ['nullable', 'json'],
            'venta' => ['nullable', 'json'],
            'alquiler' => ['nullable', 'json'],
            'condicion_alquiler' => ['nullable', 'json'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Bloquea la fila durante la actualización para serializar envíos simultáneos.
            DB::beginTransaction();

            $propiedad = Propiedad::lockForUpdate()->find($request->id);
            if (! $propiedad) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Propiedad no encontrada',
                ], 404);
            }

            // Decodificar y limpiar datos JSON del request
            $comodidades = $this->cleanArray(json_decode($request->comodidades, true) ?? []);
            $descripcion = $this->cleanArray(json_decode($request->descripcion, true) ?? []);
            $venta = $this->cleanArray(json_decode($request->venta, true) ?? []);
            $alquiler = $this->cleanArray(json_decode($request->alquiler, true) ?? []);
            $condicion_alquiler = $this->cleanArray(json_decode($request->condicion_alquiler, true) ?? []);
            $usuario_id = $request->id_usuario;

            // Solo se actualizan claves recibidas. Las claves ausentes conservan el valor en BD.
            $updates = array_merge(
                PropertyUpdateMapper::map($request->all(), [
                    'calle_id' => 'id_calle',
                    'numero_calle' => 'numero_calle',
                    'piso' => 'piso',
                    'departamento' => 'departamento',
                    'ph' => 'ph',
                    'id_inmueble' => 'id_inmueble',
                    'id_zona' => 'id_zona',
                    'id_provincia' => 'id_provincia',
                    'llave' => 'llave',
                    'comentario_llave' => 'comentario_llave',
                    'cartel' => 'cartel',
                    'comentario_cartel' => 'comentario_cartel',
                ]),
                PropertyUpdateMapper::map($comodidades, [
                    'estado_general' => 'id_estado_general',
                    'dormitorios' => 'cantidad_dormitorios',
                    'banios' => 'banios',
                    'lotes' => 'mLote',
                    'lote_cubierto' => 'mCubiertos',
                    'cochera' => 'cochera',
                    'numero_cochera' => 'numero_cochera',
                    'asfalto' => 'asfalto',
                    'gas' => 'gas',
                    'cloaca' => 'cloaca',
                    'agua' => 'agua',
                ]),
                PropertyUpdateMapper::map($descripcion, ['texto' => 'descipcion_propiedad']),
                PropertyUpdateMapper::map($venta, [
                    'asesor_resultado' => 'asesor',
                    'captador_interno_v' => 'captador_int_v',
                    'cod_venta' => 'cod_venta',
                    'estado_venta' => 'id_estado_venta',
                    'exclusividad_venta' => 'exclusividad_venta',
                    'comparte_venta' => 'comparte_venta',
                    'condicionado_venta' => 'condicionado_venta',
                    'venta_fecha_alta' => 'venta_fecha_alta',
                    'fecha_autorizacion_venta' => 'fecha_autorizacion_venta',
                    'comentario_autorizacion' => 'comentario_autorizacion',
                    'zona_prop' => 'zona_prop',
                    'flyer_v' => 'flyer_v',
                    'reel_v' => 'reel_v',
                    'web_v' => 'web_v',
                    'autorizacion_venta' => 'autorizacion_venta',
                ]),
                PropertyUpdateMapper::map($alquiler, [
                    'cod_alquiler' => 'cod_alquiler',
                    'estado_alquiler' => 'id_estado_alquiler',
                    'autorizacion_alquiler' => 'autorizacion_alquiler',
                    'fecha_autorizacion_alquiler' => 'fecha_autorizacion_alquiler',
                    'exclusividad_alquiler' => 'exclusividad_alquiler',
                    'clausula_de_venta' => 'clausula_de_venta',
                    'tiempo_clausula' => 'tiempo_clausula',
                    'alquiler_fecha_alta' => 'alquiler_fecha_alta',
                    'mascota' => 'mascota',
                    'captador_interno_a' => 'captador_int_a',
                    'flyer_a' => 'flyer_a',
                    'reel_a' => 'reel_a',
                    'web_a' => 'web_a',
                    'fecha_ofrecimiento' => 'fecha_ofrecimiento',
                ]),
                PropertyUpdateMapper::map($condicion_alquiler, ['condicion' => 'condicion'])
            );

            if (empty($updates)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron datos para actualizar la propiedad.',
                ], 422);
            }

            $propiedad->update([...$updates, 'updated_at' => now()]);

            // Actualizar datos relacionados únicamente cuando el bloque contiene precio/tasación.
            $tieneTasacion = isset(
                $venta['tasacion_venta'],
                $venta['fecha_tasacion_venta'],
                $venta['moneda_venta']
            );
            if ($tieneTasacion) {
                (new TasacionService)->crearDesdeRequest($venta, $propiedad->id);
            }

            $tienePrecioVenta = isset($venta['moneda_venta'], $venta['monto_venta']);
            $tienePrecioAlquiler = isset($alquiler['moneda_alquiler'], $alquiler['monto_alquiler']);
            if ($tienePrecioVenta || $tienePrecioAlquiler) {
                (new PrecioService)->crearDesdeRequest($venta, $alquiler, $propiedad->id);
            }

            // Manejar actualización de fotos

            if ($request->has('fotos_modificadas')) {

                $fotos_modificadas = $this->cleanArray(json_decode($request->fotos_modificadas, true));

                (new PropiedadMediaService)->modificarFoto($fotos_modificadas);
            }
            if ($request->has('fotos_eliminadas')) {
                $fotos_eliminadas = json_decode($request->fotos_eliminadas, true);
                (new PropiedadMediaService)->eliminarFoto($fotos_eliminadas);
            }
            if ($request->has('fotos_nuevas_data')) {
                (new PropiedadMediaService)->subirdesdeUpdate($request, $propiedad->id);
            }

            // Manejar actualización de documentos
            if ($request->has('documentos_modificados')) {
                $documentos_modificados = $this->cleanArray(json_decode($request->documentos_modificados, true));
                (new PropiedadMediaService)->modificarDocumento($documentos_modificados);
            }
            if ($request->has('documentos_eliminados')) {
                $documentos_eliminados = json_decode($request->documentos_eliminados, true);
                (new PropiedadMediaService)->eliminarDocumento($documentos_eliminados);
            }
            if ($request->has('documentos_nuevos_data')) {
                (new PropiedadMediaService)->subirdesdeUpdate($request, $propiedad->id);
            }

            // Manejar actualización de videos
            if ($request->has('videos_nuevos_data')) {
                //Log::info('entro a videos nuevos');
                (new PropiedadMediaService)->subirdesdeUpdate($request, $propiedad->id);
            }
            if ($request->has('videos_modificados')) {
                //Log::info('entro a videos modificados');
                $videos_modificados = $this->cleanArray(json_decode($request->videos_modificados, true));
                (new PropiedadMediaService)->modificarVideo($videos_modificados);
            }
            if ($request->has('videos_eliminados')) {
                //Log::info('entro a videos eliminados');
                $videos_eliminados = $this->cleanArray(json_decode($request->videos_eliminados));
                (new PropiedadMediaService)->eliminarVideo($videos_eliminados);
            }

            // Manejar actualización de propietarios
            if ($request->has('propietarios_eliminados')) {
                $propietarios_eliminados = json_decode($request->propietarios_eliminados, true);
                (new Propiedades_padronService)->eliminarPropietario($propiedad->id, $propietarios_eliminados);
            }
            if ($request->has('propietarios_nuevos')) {

                $propietarios_nuevos = json_decode($request->propietarios_nuevos, true);
                (new Propiedades_padronService)->vincularActualizacion($propiedad->id, $propietarios_nuevos);
            }
            if ($request->has('propietarios_modificados')) {
                $propietarios_modificados = json_decode($request->propietarios_modificados, true);
                (new Propiedades_padronService)->modificarPropietario($propiedad->id, $propietarios_modificados);
            }

            // Guardar historial solo cuando se envió al menos un estado.
            if (array_key_exists('estado_venta', $venta) || array_key_exists('estado_alquiler', $alquiler)) {
                $this->propiedadService->guardarHistorialEstadosSerbive(
                    $propiedad->id,
                    $venta['estado_venta'] ?? null,
                    $alquiler['estado_alquiler'] ?? null,
                    $alquiler['descripcion_estado_alquiler'] ?? null,
                    $venta['descripcion_estado_venta'] ?? null,
                    $alquiler['fecha_baja_temporal_alquiler'] ?? null,
                    $venta['fecha_baja_temporal_venta'] ?? null,
                    $usuario_id
                );
            }

            // Actualizar folios de empresas
            $clavesFolios = ['FCentral', 'FCandioti', 'FTribunales'];
            $foliosEnviados = array_intersect_key($alquiler, array_flip($clavesFolios));
            $folios = [
                1 => $alquiler['FCentral'] ?? null,
                2 => $alquiler['FCandioti'] ?? null,
                3 => $alquiler['FTribunales'] ?? null,
            ];

            if ($foliosEnviados !== [] && in_array(true, array_map(
                fn($folio) => $folio !== '-',
                $foliosEnviados
            ), true)) {
                $this->empresaPropiedadService->actualizarFolioExistente(
                    $propiedad->id,
                    $folios
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad actualizada correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la propiedad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descarga todas las fotos de una propiedad en un archivo ZIP
     *
     * Este método busca las fotos físicas en la red y las comprime
     * en un archivo ZIP para su descarga.
     *
     * @param  int  $id  ID de la propiedad
     * @return \Illuminate\Http\StreamedResponse|\Illuminate\Http\JsonResponse Archivo ZIP o error
     */
    public function descargarFotos($id)
    {
        try {
            // Obtener fotos de la propiedad
            $fotos = Foto::where('propiedad_id', $id)->get();

            if ($fotos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron fotos para esta propiedad.',
                ], 404);
            }

            // Obtener información de la propiedad para el nombre del archivo
            $propiedad = $this->propiedadService->obtenerPropiedadesPorId($id);
            $calle = Calle::find($propiedad->id_calle);
            $numero = $propiedad->numero_calle;

            // Limpiar nombre del archivo para evitar caracteres inválidos
            $calleName = preg_replace('/[^a-zA-Z0-9\s]/', '', $calle->name);
            $zipFileName = trim($calleName) . '-' . $numero . '.zip';

            // Crear y enviar el archivo ZIP
            return response()->streamDownload(function () use ($fotos, $calleName, $numero) {
                $zip = new \ZipStream\ZipStream(
                    outputName: $calleName . '-' . $numero . '.zip',
                    sendHttpHeaders: false
                );

                $basePath = '\\\\10.10.10.151\\Compartida\\PROPIEDADES';
                $filesAdded = 0;
                $filesNotFound = 0;

                foreach ($fotos as $foto) {
                    $imagePath = str_replace('/imagenes', '', $foto->url);
                    $filePath = $basePath . str_replace('/', '\\', $imagePath);

                    if (file_exists($filePath)) {
                        $fileName = basename($filePath);
                        $zip->addFileFromPath($fileName, $filePath);
                        $filesAdded++;
                    } else {
                        $filesNotFound++;
                    }
                }

                if ($filesAdded > 0) {
                    $zip->finish();
                } else {
                    echo 'No se encontraron archivos físicos para comprimir.';
                }
            }, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la descarga de fotos.',
            ], 500);
        }
    }

    /**
     * Actualiza las observaciones de una propiedad registrando una novedad
     *
     * Este método inserta un registro en la tabla observaciones_propiedades
     * dentro de una transacción para asegurar consistencia de datos.
     *
     * @param  Request  $request  Datos del formulario de observaciones
     * @param  string|int  $propiedad_id  ID de la propiedad a actualizar
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
     */
    public function update(Request $request, string $propiedad_id)
    {
        DB::beginTransaction();

        try {
            $formulario = $request->input('formulario');  // Obtener qué formulario se envió
            $novedades = $request->input('novedad');       // Obtener el valor del textarea
            $fecha_actual = $request->input('fecha_actual');
            $usuario_id = $request->input('usuario_id_nov');

            // Determinar tipo de oferta: V = venta, A = alquiler
            $tipo_oferta = ($formulario === 'novedavesVentas') ? 'V' : 'A';

            // Inserción del registro en la tabla de observaciones
            DB::table('observaciones_propiedades')->insert([
                'propiedad_id' => $propiedad_id,
                'notes' => $novedades,
                'tipo_ofera' => $tipo_oferta,
                'created_at' => $fecha_actual,
                'updated_at' => now(),
                'last_modified_by' => $usuario_id,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Novedad cargada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error al guardar la novedad.');
        }
    }

    /**
     * Guarda una nueva novedad/observación para una propiedad
     *
     * Este método crea un nuevo registro de observación en la base de datos
     * con la información proporcionada desde el formulario.
     *
     * @param  Request  $request  Datos de la novedad a guardar
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado
     */
    public function guardarNovedad(Request $request)
    {
        try {
            $novedad = Observaciones_propiedades::create([
                'propiedad_id' => $request->propiedad_id,
                'notes' => $request->notes,
                'tipo_ofera' => $request->tipo_ofera,
                'created_at' => now(),
                'last_modified_by' => $request->user_id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $novedad,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la novedad: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Busca propiedades según código, calle o sector
     *
     * Este método realiza una búsqueda simple de propiedades
     * aplicando filtros básicos según los parámetros proporcionados.
     *
     * @param  Request  $request  Parámetros de búsqueda (código, calle, sector)
     * @return \Illuminate\Http\JsonResponse Lista de propiedades encontradas
     */
    public function search(Request $request)
    {
        try {
            $codigo = $request->query('codigo', '');
            $calle = $request->query('calle', '');
            $sector = $request->query('sector_asesor', '');

            // Buscar propiedades usando el servicio
            $props = $this->propiedadService->buscarPropiedades($codigo, $calle, $sector);

            // Convertir a colección si no lo es
            $props = collect($props);

            // Aplicar filtros según el sector
            if ($sector === 'venta') {
                $props = $props->filter(function ($prop) {
                    return ! is_null($prop['cod_venta'] ?? null);
                });
            } elseif ($sector === 'alquiler') {
                $props = $props->filter(function ($prop) {
                    return ! is_null($prop['cod_alquiler'] ?? null);
                });
            }

            return response()->json($props->values());
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda',
            ], 500);
        }
    }

    /**
     * Busca propiedades de venta por código o calle
     */
    public function buscarPropiedadesVenta(Request $request)
    {
        //Log::info('Buscando propiedades de venta', $request->all());
        try {
            $tipo = $request->get('tipo', 'venta');
            $codigo = $request->get('codigo', '');
            $calle = $request->get('calle', '');
            $dormitorios = $request->get('dorm') ? (int) $request->get('dorm') : null;
            $banios = $request->get('baños') ? (int) $request->get('baños') : null;
            $cochera = $request->get('cochera', '');

            if ($tipo === 'alquiler') {
                $propiedades = $this->propiedadService->buscarPropiedadesAlquilerCompleto($codigo, $calle, $dormitorios, $banios, $cochera);
            } else {
                $propiedades = $this->propiedadService->buscarPropiedadesVenta($codigo, $calle, $dormitorios, $banios, $cochera);
            }

            return response()->json([
                'success' => true,
                'data' => $propiedades,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar propiedades: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Limpia un array o valor eliminando cadenas vacías y convirtiéndolas a null
     *
     * Este método recursivo limpia datos de formularios para asegurar que
     * las cadenas vacías se almacenen como null en la base de datos.
     *
     * @param  mixed  $data  Datos a limpiar (array o valor simple)
     * @return mixed Datos limpios con cadenas vacías convertidas a null
     */
    public function cleanArray($data)
    {
        if (! is_array($data)) {
            return $data === '' ? null : $data;
        }

        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = $this->cleanArray($value);
            } else {
                $cleaned[$key] = $value === '' ? null : $value;
            }
        }

        return $cleaned;
    }

    public function fichaPropiedad(Request $request)
    {
        // Los datos que antes pasabas por props en Vue
        $propiedad = $request->propiedad;
        $ubicacion = $request->ubicacion;
        $usuario_id = auth('api')->id();
        $username = Usuario::where('id', $usuario_id)->first()->username;
        $fotosOrdenadas = [];
        // Ordenar fotos por campo 'orden', null al final
        usort($propiedad['fotos'], function ($a, $b) {
            $ordenA = $a['orden'] ?? PHP_INT_MAX;
            $ordenB = $b['orden'] ?? PHP_INT_MAX;

            return $ordenA <=> $ordenB;
        });
        // Tomar las primeras 3 fotos
        foreach ($propiedad['fotos'] as $foto) {
            if (count($fotosOrdenadas) < 3) {
                $fotosOrdenadas[] = $foto['url'];
            }
        }

        // Generamos el HTML usando una vista de Blade limpia
        $html = view('pdfs.atcl.ficha_propiedad', compact('propiedad', 'ubicacion', 'fotosOrdenadas'))->render();

        return response()->streamDownload(function () use ($html, $username) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->emulateMedia('screen')
                ->showBackground()
                ->setOption('displayHeaderFooter', true)
                ->setOption('headerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Ficha de Propiedad</span></div>')
                ->setOption('footerTemplate', '<div style="font-size:10px; color:#666; width:100%; display:flex; justify-content:space-between; padding:0 20px;"><span style="text-align:left;">Salas Inmobiliaria</span><span style="text-align:center;">' . $username . '</span>  <span style="text-align:right;" class="date"></span></div>')
                ->pdf();
        }, 'ficha_propiedad.pdf');
    }

    /**
     * Procesa los impuestos (Agua, TGI, API) para cada folio de la propiedad
     *
     * @param \Illuminate\Database\Eloquent\Collection $folios
     * @return array Datos estructurados de impuestos por folio
     */
    private function procesarImpuestosPorFolios($folios)
    {
        $impuestosData = [];

        foreach ($folios as $folio) {
            $folioData = [
                'folio' => $folio->folio,
                'empresa_id' => $folio->empresa_id,
                'impuestos' => []
            ];

            // Definir configuración de impuestos
            $impuestosConfig = [
                'agua' => [
                    'padron_model' => Agua_padron::class,
                    'carga_model' => Agua_carga::class,
                    'fields' => ['partida', 'administra', 'clave', 'importe']
                ],
                'tgi' => [
                    'padron_model' => Tgi_padron::class,
                    'carga_model' => Tgi_carga::class,
                    'fields' => ['partida', 'administra', 'clave', 'importe']
                ],
                'api' => [
                    'padron_model' => Api_padron::class,
                    'carga_model' => Api_carga::class,
                    'fields' => ['partida', 'administra', 'importe']
                    // API no tiene 'clave'
                ],
                'gas' => [
                    'padron_model' => Gas_padron::class,
                    'carga_model' => Gas_carga::class,
                    'fields' => ['partida', 'administra', 'clave', 'importe']
                ]
            ];

            foreach ($impuestosConfig as $tipo => $config) {
                // Obtener datos del padrón
                $padronData = $config['padron_model']::where('folio', $folio->folio)
                    ->where('empresa', $folio->empresa_id)
                    ->get();

                foreach ($padronData as $item) {
                    // Obtener última carga
                    $ultimaCarga = $this->obtenerUltimaCargaImpuesto(
                        $config['carga_model'],
                        $folio->folio,
                        $folio->empresa_id
                    );
                    //Log::info('informacion de ultimacargar', [$ultimaCarga]);

                    // Construir datos del impuesto
                    $impuestoItem = [
                        'tipo' => $tipo,
                        'partida' => $item->partida,
                        'administra' => $item->administra ?? null,
                        //'importe' => $item->importe ?? null,
                    ];

                    // Agregar 'clave' solo si existe en el modelo
                    if (in_array('clave', $config['fields'])) {
                        //Log::info(' ultima  clave verificar', [$item->clave]);
                        $impuestoItem['clave'] = $item->clave ?? null;
                    }
                    //Log::info('ultima clave verificar', [$impuestoItem]);

                    // Agregar datos de la última carga
                    if ($ultimaCarga) {
                        $impuestoItem['ultima_carga'] = [
                            'fecha_vencimiento' => $ultimaCarga->fecha_vencimiento ?? null,
                            'importe' => $ultimaCarga->importe ?? null,
                        ];
                    } else {
                        $impuestoItem['ultima_carga'] = null;
                    }

                    $folioData['impuestos'][] = $impuestoItem;
                }
            }

            // Solo agregar folios que tengan datos de impuestos
            if (!empty($folioData['impuestos'])) {
                $impuestosData[] = $folioData;
            }
        }

        return $impuestosData;
    }

    /**
     * Obtiene la última carga de un impuesto específico
     *
     * @param string $modelo Clase del modelo de carga
     * @param string $folio Número de folio
     * @param int $empresa ID de la empresa
     * @return object|null Modelo de carga o null si no existe
     */
    private function obtenerUltimaCargaImpuesto($modelo, $folio, $empresa)
    {
        /* Log::info('este es el modelo', [$modelo]);
        Log::info('este es el folio', [$folio]);
        Log::info('esta es la empresa', [$empresa]); */
        return $modelo::where('compartidos', 'LIKE', '%"folio":' . $folio . '%')
            ->where('compartidos', 'LIKE', '%"empresa":' . $empresa . '%')
            ->orderByDesc('fecha_vencimiento')
            ->first();
    }
}
