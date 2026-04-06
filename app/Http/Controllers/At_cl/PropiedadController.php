<?php

namespace App\Http\Controllers\At_cl;

use Illuminate\Http\Request;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Calle;
use App\Models\At_cl\Foto;
use App\Models\At_cl\Observaciones_propiedades;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\At_cl\PrecioService;
use App\Services\At_cl\PropiedadService;
use App\Services\At_cl\TasacionService;
use App\Services\At_cl\Propiedades_padronService;
use App\Services\At_cl\FiltroPropiedadService;
use App\Services\At_cl\PropiedadMediaService;
use Illuminate\Support\Facades\Log;
use App\Services\At_cl\EmpresaPropiedadService;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use App\Services\contable\sellado\RegistroSelladoService;



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
    protected $propiedad_padronService, $propiedadService, $empresaPropiedadService;


    /**
     * Constructor del controlador - Inicializa servicios necesarios
     *
     * @param PropiedadService $propiedadService Servicio de gestión de propiedades
     * @param Propiedades_padronService $propiedad_padronService Servicio de padrón de propiedades
     * @param EmpresaPropiedadService $empresaPropiedadService Servicio de empresas de propiedades
     */
    public function __construct(
        PropiedadService $propiedadService,
        Propiedades_padronService $propiedad_padronService,
        EmpresaPropiedadService $empresaPropiedadService,
        protected  RegistroSelladoService $registro_sellado,
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
     * @param Request $request Datos del formulario
     * @param int $id ID del usuario que crea la propiedad
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
                'cod_venta'      => $venta['cod_venta'] ?? null,
                'cod_alquiler'   => $alquiler['cod_alquiler'] ?? null,
                'calle_id'       => $request->calle_id,
                'numero_calle'   => $request->altura,
                'piso'           => $request->piso,
                'departamento'   => $request->dto,
                'llave'          => $request->llave,
                'dormitorios'    => $comodidades['dormitorios'] ?? null,
                'banios'         => $comodidades['banios'] ?? null,
                'lotes'          => $comodidades['lotes'] ?? null,
                'lote_cubierto'  => $comodidades['lote_cubierto'] ?? null,
                'numero_cochera' => $comodidades['numero_cochera'] ?? null,
                'monto_venta'    => $venta['monto_venta'] ?? null,
                'folio_central'      => $alquiler['FCentral'] ?? null,
                'folio_candioti'     => $alquiler['FCandioti'] ?? null,
                'folio_tribunales'   => $alquiler['FTribunales'] ?? null,
                'venta_fecha_alta'         => $venta['venta_fecha_alta'] ?? null,
                'fecha_autorizacion_venta' => $venta['fecha_autorizacion_venta'] ?? null,
                'alquiler_fecha_alta'         => $alquiler['alquiler_fecha_alta'] ?? null,
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
                'folio_central'    => ['nullable', 'regex:/^[0-9]+$/'],
                'folio_candioti'   => ['nullable', 'regex:/^[0-9]+$/'],
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
                'errors'  => $validator->errors(),
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
            $propiedad_creada = (new PropiedadService())->crearPropiedad($datos, $id);

            // Crear tasación si hay datos de venta
            (new TasacionService())->crearDesdeRequest($venta, $propiedad_creada->id);

            // Crear registro de precios
            (new PrecioService())->crearDesdeRequest($venta, $alquiler, $propiedad_creada->id);

            // Carga de archivos multimedia
            (new PropiedadMediaService())->subirDesdeRequest($request, $propiedad_creada->id);

            // Asociación de la propiedad a empresas (folios)
            $folios = [
                1 => $alquiler['FCentral'] ?? null,
                2 => $alquiler['FCandioti'] ?? null,
                3 => $alquiler['FTribunales'] ?? null,
            ];

            if (($alquiler['FCentral'] ?? null) != null || ($alquiler['FCandioti'] ?? null) != null || ($alquiler['FTribunales'] ?? null) != null) {
                (new EmpresaPropiedadService())->asociarNuevoFolio(array($folios), $propiedad_creada->id);
            }

            // Asociación de la propiedad con los propietarios
            if (!empty($propietario)) {
                $propietario_decoded = is_array($propietario) ? $propietario : json_decode($propietario, true);

                if ($propietario_decoded) {
                    foreach ($propietario_decoded as $propietario_item) {
                        if (isset($propietario_item['id'])) {
                            $this->propiedad_padronService->vincularActualizacion($propiedad_creada->id, [$propietario_item]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Propiedad guardada correctamente.',
                'data'     => [
                    'id' => $propiedad_creada->id,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la propiedad: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Busca propiedades según filtros avanzados
     *
     * Este método aplica filtros múltiples y ordenamiento para encontrar propiedades
     * que coincidan con los criterios de búsqueda especificados.
     *
     * @param Request $request Parámetros de filtrado y búsqueda
     * @param FiltroPropiedadService $filtroService Servicio de filtrado
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
                'precio_alquiler' => $propiedad->precioActual?->moneda_alquiler_pesos ?? $propiedad->precioActual?->moneda_alquiler_dolar,
                'precio_venta' => $propiedad->precioActual?->moneda_venta_dolar ?? $propiedad->precioActual?->moneda_venta_pesos,
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
     * @param Request $request Contiene el ID de la propiedad a mostrar
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
                'precioActual',
                'tasaciones',
                'usuarioAsesor',
                'usuarioCaptadorInt',
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
                    'message' => 'Propiedad no encontrada'
                ], 404);
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
            $propiedadArray['buscarFolioActivo'] = $foliosActivos;
            $propiedadArray['buscarContratoMasReciente'] = $contratoMasReciente;
            $propiedadArray['detalleContrato'] = $detalleContrato;

            // Eliminar valores null para limpiar la respuesta
            $propiedadFiltrada = array_filter($propiedadArray, function ($value) {
                return $value !== null;
            });


            // 1. Obtener el ID del usuario autenticado vía JWT/Token
            $usuario_id = auth('api')->id();

            // 2. Instanciar el servicio de permisos localmente
            $accessService = new PermitirAccesoSelladoService($usuario_id);
            // 3. Recopilación de Permisos de Botones
            $botones = [
                'propietario' => $accessService->tieneAcceso('propietario'),
                'informacion_venta' => $accessService->tieneAcceso('informacion_venta'),
                'informacion_alquiler' => $accessService->tieneAcceso('informacion_alquiler'),
                'modificar' => $accessService->tieneAcceso('modificar')
            ];
            //Log::info('despues del array de botones', $botones);
           // $resultado = $this->registro_sellado->getRegistroSellado();

            //Log::info('despues de resultado', $resultado);
            return response()->json([
                'success' => true,
                'data' => $propiedadFiltrada,
                'botones' => $botones
                /* 'permisos' => [
                    'botones' => $botones,
                    'registro_sellado' => $resultado
                ] */
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos de la propiedad'
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
     * @param Request $request Datos actualizados de la propiedad
     * @return \Illuminate\Http\JsonResponse Resultado de la operación
     */
    public function actualizarPropiedad(Request $request)
    {
        try {
            $propiedad = Propiedad::find($request->id);
            if (!$propiedad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propiedad no encontrada'
                ], 404);
            }

            // Iniciar transacción para garantizar consistencia
            DB::beginTransaction();

            // Decodificar y limpiar datos JSON del request
            $comodidades = $this->cleanArray(json_decode($request->comodidades, true) ?? []);
            $descripcion = $this->cleanArray(json_decode($request->descripcion, true) ?? []);
            $venta = $this->cleanArray(json_decode($request->venta, true) ?? []);
            $alquiler = $this->cleanArray(json_decode($request->alquiler, true) ?? []);
            $condicion_alquiler = $this->cleanArray(json_decode($request->condicion_alquiler, true) ?? []);
            $usuario_id = $request->id_usuario;

            // Actualizar datos básicos de la propiedad
            $propiedad->update([
                'id_calle'                    => $request->calle_id ?? null,
                'numero_calle'                => $request->numero_calle ?? null,
                'piso'                        => $request->piso ?? null,
                'departamento'                => $request->departamento ?? null,
                'ph'                          => $request->ph ?? null,
                'id_inmueble'                 => $request->id_inmueble ?? null,
                'id_zona'                     => $request->id_zona ?? null,
                'id_provincia'                => $request->id_provincia ?? null,
                'llave'                       => $request->llave ?? null,
                'comentario_llave'            => $request->comentario_llave ?? null,
                'cartel'                      => $request->cartel ?? null,
                'comentario_cartel'           => $request->comentario_cartel ?? null,
                // Actualizar campos de comodidades
                'id_estado_general'           => $comodidades['estado_general'] ?? null,
                'cantidad_dormitorios'        => $comodidades['dormitorios'] ?? null,
                'banios'                      => $comodidades['banios'] ?? null,
                'mLote'                       => $comodidades['lotes'] ?? null,
                'mCubiertos'                  => $comodidades['lote_cubierto'] ?? null,
                'cochera'                     => $comodidades['cochera'] ?? null,
                'numero_cochera'              => $comodidades['numero_cochera'] ?? null,
                'asfalto'                     => $comodidades['asfalto'] ?? null,
                'gas'                         => $comodidades['gas'] ?? null,
                'cloaca'                      => $comodidades['cloaca'] ?? null,
                'agua'                        => $comodidades['agua'] ?? null,
                // Actualizar descripción
                'descipcion_propiedad'        => $descripcion['texto'] ?? null,
                // Actualizar datos de venta
                'asesor'                      => $venta['asesor_resultado'] ?? null,
                'captador_int'                => $venta['captador_interno'] ?? null,
                'cod_venta'                   => $venta['cod_venta'] ?? null,
                'id_estado_venta'             => $venta['estado_venta'] ?? null,
                'exclusividad_venta'          => $venta['exclusividad_venta'] ?? null,
                'comparte_venta'              => $venta['comparte_venta'] ?? null,
                'condicionado_venta'          => $venta['condicionado_venta'] ?? null,
                'venta_fecha_alta'            => $venta['venta_fecha_alta'] ?? null,
                'fecha_autorizacion_venta'    => $venta['fecha_autorizacion_venta'] ?? null,
                'comentario_autorizacion'     => $venta['comentario_autorizacion'] ?? null,
                'zona_prop'                   => $venta['zona_prop'] ?? null,
                'flyer'                       => $venta['flyer'] ?? null,
                'reel'                        => $venta['reel'] ?? null,
                'web'                         => $venta['web'] ?? null,
                'autorizacion_venta'          => $venta['autorizacion_venta'] ?? null,
                // Actualizar datos de alquiler
                'cod_alquiler'                => $alquiler['cod_alquiler'] ?? null,
                'id_estado_alquiler'          => $alquiler['estado_alquiler'] ?? null,
                'autorizacion_alquiler'       => $alquiler['autorizacion_alquiler'] ?? null,
                'fecha_autorizacion_alquiler' => $alquiler['fecha_autorizacion_alquiler'] ?? null,
                'exclusividad_alquiler'       => $alquiler['exclusividad_alquiler'] ?? null,
                'clausula_de_venta'           => $alquiler['clausula_de_venta'] ?? null,
                'tiempo_clausula'             => $alquiler['tiempo_clausula'] ?? null,
                'alquiler_fecha_alta'         => $alquiler['alquiler_fecha_alta'] ?? null,
                'mascota'                     => $alquiler['mascota'] ?? null,
                // Condición de alquiler
                'condicion'                   => $condicion_alquiler['condicion'] ?? null
            ]);

            // Actualizar datos relacionados con servicios
            (new TasacionService())->crearDesdeRequest($venta, $propiedad->id);
            (new PrecioService())->crearDesdeRequest($venta, $alquiler, $propiedad->id);

            // Manejar actualización de fotos
            Log::info('fotos_modificadas', ['fotos_modificadas' => $request->fotos_modificadas]);
            if ($request->has('fotos_modificadas')) {

                $fotos_modificadas = $this->cleanArray(json_decode($request->fotos_modificadas, true));
                Log::info('fotos_modificadas_clean', ['fotos_modificadas' => $fotos_modificadas]);
                (new PropiedadMediaService())->modificarFoto($fotos_modificadas);
            }
            if ($request->has('fotos_eliminadas')) {
                $fotos_eliminadas = json_decode($request->fotos_eliminadas, true);
                (new PropiedadMediaService())->eliminarFoto($fotos_eliminadas);
            }
            if ($request->has('fotos_nuevas_data')) {
                (new PropiedadMediaService())->subirdesdeUpdate($request, $propiedad->id);
            }

            // Manejar actualización de documentos
            if ($request->has('documentos_modificados')) {
                $documentos_modificados = $this->cleanArray(json_decode($request->documentos_modificados, true));
                (new PropiedadMediaService())->modificarDocumento($documentos_modificados);
            }
            if ($request->has('documentos_eliminados')) {
                $documentos_eliminados = json_decode($request->documentos_eliminados, true);
                (new PropiedadMediaService())->eliminarDocumento($documentos_eliminados);
            }
            if ($request->has('documentos_nuevos_data')) {
                (new PropiedadMediaService())->subirdesdeUpdate($request, $propiedad->id);
            }

            // Manejar actualización de videos
            if ($request->has('videos_nuevos_data')) {
                (new PropiedadMediaService())->subirdesdeUpdate($request, $propiedad->id);
            }
            if ($request->has('videos_modificados')) {
                $videos_modificados = $this->cleanArray(json_decode($request->videos_modificados, true));
                (new PropiedadMediaService())->modificarVideo($videos_modificados);
            }
            if ($request->has('videos_eliminados')) {
                $videos_eliminados = $this->cleanArray(json_decode($request->videos_eliminados));
                (new PropiedadMediaService())->eliminarVideo($videos_eliminados);
            }

            // Manejar actualización de propietarios
            if ($request->has('propietarios_eliminados')) {
                $propietarios_eliminados = json_decode($request->propietarios_eliminados, true);
                (new Propiedades_padronService())->eliminarPropietario($propiedad->id, $propietarios_eliminados);
            }
            if ($request->has('propietarios_nuevos')) {
                $propietarios_nuevos = json_decode($request->propietarios_nuevos, true);
                (new Propiedades_padronService())->vincularActualizacion($propiedad->id, $propietarios_nuevos);
            }
            if ($request->has('propietarios_modificados')) {
                $propietarios_modificados = json_decode($request->propietarios_modificados, true);
                (new Propiedades_padronService())->modificarPropietario($propiedad->id, $propietarios_modificados);
            }

            // Guardar historial de estados
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

            // Actualizar folios de empresas
            $folios = [
                1 => $alquiler['FCentral'] ?? null,
                2 => $alquiler['FCandioti'] ?? null,
                3 => $alquiler['FTribunales'] ?? null,
            ];

            if ($alquiler['FCentral'] != '-' || $alquiler['FCandioti'] != '-' || $alquiler['FTribunales'] != '-') {
                $this->empresaPropiedadService->actualizarFolioExistente(
                    $propiedad->id,
                    $folios
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Propiedad actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la propiedad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga todas las fotos de una propiedad en un archivo ZIP
     *
     * Este método busca las fotos físicas en la red y las comprime
     * en un archivo ZIP para su descarga.
     *
     * @param int $id ID de la propiedad
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
                    'message' => 'No se encontraron fotos para esta propiedad.'
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
                    echo "No se encontraron archivos físicos para comprimir.";
                }
            }, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la descarga de fotos.'
            ], 500);
        }
    }

    /**
     * Actualiza las observaciones de una propiedad registrando una novedad
     *
     * Este método inserta un registro en la tabla observaciones_propiedades
     * dentro de una transacción para asegurar consistencia de datos.
     *
     * @param Request $request Datos del formulario de observaciones
     * @param string|int $propiedad_id ID de la propiedad a actualizar
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
     * @param Request $request Datos de la novedad a guardar
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado
     */
    public function guardarNovedad(Request $request)
    {
        try {
            $novedad = Observaciones_propiedades::create([
                'propiedad_id' => $request->propiedad_id,
                'notes'        => $request->notes,
                'tipo_ofera'   => $request->tipo_ofera,
                'created_at'   => now(),
                'last_modified_by' => $request->user_id
            ]);

            return response()->json([
                'success' => true,
                'data'    => $novedad
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la novedad: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Guarda un cambio en la sesión del usuario
     *
     * Este método almacena temporalmente valores en la sesión
     * para mantener estado entre solicitudes.
     *
     * @param Request $request Contiene el campo y valor a guardar
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el resultado
     */
    public function guardarCambio(Request $request)
    {
        // Validar que el campo y el valor se han enviado correctamente
        $request->validate([
            'campo' => 'required|string',
            'valor' => 'required|string',
        ]);

        try {
            // Almacenar el cambio en la sesión
            session()->put($request->campo, $request->valor);
            session()->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cambio guardado correctamente en la sesión.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar el cambio.'
            ], 500);
        }
    }


    /**
     * Busca propiedades según código, calle o sector
     *
     * Este método realiza una búsqueda simple de propiedades
     * aplicando filtros básicos según los parámetros proporcionados.
     *
     * @param Request $request Parámetros de búsqueda (código, calle, sector)
     * @return \Illuminate\Http\JsonResponse Lista de propiedades encontradas
     */
    public function search(Request $request)
    {
        try {
            $codigo = $request->query('codigo', '');
            $calle  = $request->query('calle', '');
            $sector = $request->query('sector_asesor', '');

            // Buscar propiedades usando el servicio
            $props = $this->propiedadService->buscarPropiedades($codigo, $calle, $sector);

            // Convertir a colección si no lo es
            $props = collect($props);

            // Aplicar filtros según el sector
            if ($sector === 'venta') {
                $props = $props->filter(function ($prop) {
                    return !is_null($prop['cod_venta'] ?? null);
                });
            } elseif ($sector === 'alquiler') {
                $props = $props->filter(function ($prop) {
                    return !is_null($prop['cod_alquiler'] ?? null);
                });
            }

            return response()->json($props->values());

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda'
            ], 500);
        }
    }

    /**
     * Busca propiedades de venta por código o calle
     */
    public function buscarPropiedadesVenta(Request $request)
    {
        Log::info('Buscando propiedades de venta', $request->all());
        try {
            $codigo = $request->get('codigo', '');
            $calle = $request->get('calle', '');
            $dormitorios = $request->get('dorm') ? (int)$request->get('dorm') : null;
            $banios = $request->get('baños') ? (int)$request->get('baños') : null;
            $cochera = $request->get('cochera', '');

            $propiedades = $this->propiedadService->buscarPropiedadesVenta($codigo, $calle, $dormitorios, $banios, $cochera);

            return response()->json([
                'success' => true,
                'data' => $propiedades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar propiedades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpia un array o valor eliminando cadenas vacías y convirtiéndolas a null
     *
     * Este método recursivo limpia datos de formularios para asegurar que
     * las cadenas vacías se almacenen como null en la base de datos.
     *
     * @param mixed $data Datos a limpiar (array o valor simple)
     * @return mixed Datos limpios con cadenas vacías convertidas a null
     */
    public function cleanArray($data)
    {
        if (!is_array($data)) {
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


}
