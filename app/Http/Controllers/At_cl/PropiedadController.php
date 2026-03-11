<?php

namespace App\Http\Controllers\At_cl;

use App\Http\Requests\FiltrarPropiedadRequest;
use App\Models\At_cl\Tasacion;
use Illuminate\Http\Request;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Tipo_inmueble;
use App\Models\At_cl\Zona;
use App\Models\At_cl\Calle;
use App\Models\At_cl\Barrio;
use App\Models\At_cl\Estado_alquiler;
use App\Models\At_cl\estado_general;
use App\Models\At_cl\Estado_venta;
use App\Models\At_cl\Foto;
use App\Models\At_cl\Localidad;
use App\Models\At_cl\Observaciones_propiedades;
use App\Models\At_cl\Precio;
use App\Models\At_cl\Provincia;
use App\Models\sys\Propiedades_sys;
use App\Models\sys\Contratos_cabecera_sys;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\At_cl\Padron;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StorePropiedadRequest;
use App\Models\At_cl\Documentacion;
use App\Models\At_cl\Empresas_propiedades;
use App\Models\usuarios_y_permisos\Usuario;
use App\Models\At_cl\Video;
use App\Models\cliente\Usuario_sector;
use App\Services\At_cl\documentacionService;
use App\Services\At_cl\FotosService;
use App\Services\At_cl\HistorialFechasService;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Services\At_cl\PrecioService;
use App\Services\At_cl\PropiedadService;
use App\Services\At_cl\ObservacionesPropiedadesService;
use App\Services\At_cl\TasacionService;
use App\Services\At_cl\Propiedades_padronService;
use App\Services\At_cl\VideosService;
use Illuminate\Database\QueryException;
use App\Services\At_cl\FiltroPropiedadService;
use App\Services\At_cl\PropiedadMediaService;
use App\Services\At_cl\EmpresaPropiedadService;



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
    protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
        $estado_venta, $localidad, $barrio, $Propiedades, $contrato_cabecera, $observaciones_propiedades, $provincia,
        $padron, $precio,  $usuario_id, $accessService, $propiedadService, $precioService, $observacionesPropiedadesService,
        $usuario, $fotoService, $documentacionService, $historialFechasService, $tasacionService, $propiedad_padronService,
        $videoService, $filtroPropiedadService, $mediaService, $empresaPropiedadService;


    public function __construct(
        FiltroPropiedadService $filtroPropiedadService,
        PropiedadService $propiedadService,
        PrecioService $precioService,
        ObservacionesPropiedadesService $observacionesService,
        FotosService $fotoService,
        documentacionService $documentacionService,
        HistorialFechasService $historialFechasService,
        TasacionService $tasacionService,
        Propiedades_padronService $propiedad_padronService,
        VideosService $videoService,
        PropiedadMediaService $mediaService,
        EmpresaPropiedadService $empresaPropiedadService
    ) {
        // Definir variables globales para todas las funciones
        $this->tipo_inmueble = Tipo_inmueble::all();
        $this->zona = Zona::all();
        $this->calle = Calle::all();
        $this->barrio = Barrio::all();
        $this->estado_alquileres = Estado_alquiler::all();
        $this->estado_general = Estado_general::all();
        $this->estado_venta = Estado_venta::all();
        $this->localidad = Localidad::all();
        $this->observaciones_propiedades = Observaciones_propiedades::all();
        $this->provincia = Provincia::all();
        $this->padron = Padron::all();
        $this->precio = Precio::all();
        $this->Propiedades = Propiedades_sys::all();
        $this->contrato_cabecera = Contratos_cabecera_sys::all();
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
        $this->propiedadService = $propiedadService;
        $this->precioService = $precioService;
        $this->observacionesPropiedadesService = $observacionesService;
        $this->fotoService = $fotoService;
        $this->documentacionService = $documentacionService;
        $this->historialFechasService = $historialFechasService;
        $this->tasacionService = $tasacionService;
        $this->propiedad_padronService = $propiedad_padronService;
        $this->videoService = $videoService;
        $this->filtroPropiedadService = $filtroPropiedadService;
        $this->mediaService = $mediaService;
        $this->empresaPropiedadService = $empresaPropiedadService;
    }





    public function guardarPropiedad(Request $request, $id)
    {

        $comodidades = $this->cleanArray(json_decode($request->comodidades, true) ?? []);
        $descripcion = $this->cleanArray(json_decode($request->descripcion, true) ?? []);
        $venta = $this->cleanArray(json_decode($request->venta, true) ?? []);
        $alquiler = $this->cleanArray(json_decode($request->alquiler, true) ?? []);
        $condicionAlquiler = $this->cleanArray(json_decode($request->condicion_alquiler, true) ?? []);
        $propietario = $this->cleanArray(json_decode($request->propietario, true) ?? []);
        $novedades = $this->cleanArray(json_decode($request->novedades, true) ?? []);
        //Log::info('propieatios: ' . json_encode($propietario));
        Log::info($request);


        //Validaciones basicas para el guardado de la propiedad

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

            //Log::info($datos);
            // Crear la propiedad usando el servicio
            $propiedad_creada = (new PropiedadService())->crearPropiedad($datos, $id);


            (new TasacionService())->crearDesdeRequest($venta, $propiedad_creada->id);


            (new PrecioService())->crearDesdeRequest($venta, $alquiler, $propiedad_creada->id);

            // Carga de archivos multimedia

            (new PropiedadMediaService())->subirDesdeRequest($request, $propiedad_creada->id);

            // Asociación de la propiedad a empresas

            $folios = [
                1 => $alquiler['FCentral'] ?? null,
                2 => $alquiler['FCandioti'] ?? null,
                3 => $alquiler['FTribunales'] ?? null,
            ];

            if (($alquiler['FCentral'] ?? null) != null || ($alquiler['FCandioti'] ?? null) != null || ($alquiler['FTribunales'] ?? null) != null) {

                (new EmpresaPropiedadService())->asociarNuevoFolio(array($folios), $propiedad_creada->id);
            }
            //Asociacion de la propiedad con los propietarios

            if (!empty($propietario)) {
                // Verificar si ya es un array o si necesita decodificación
                $propietario_decoded = is_array($propietario) ? $propietario : json_decode($propietario, true);

                if ($propietario_decoded) {
                    foreach ($propietario_decoded as $propietario_item) {
                        if (isset($propietario_item['id'])) {
                            // Usar el método vincularActualizacion que maneja los datos del pivot
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
            Log::error("Error al guardar propiedad: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * ESTO CORRESPONDE AL FILTRADO
    **/
    public function buscaPropiedad(Request $request, FiltroPropiedadService $filtroService)
    {
        //Log::info($request->all());

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
        /*  Log::info($propiedades); */

        //quiero enviar el name de la calle
        foreach ($propiedades as $propiedad) {
            $propiedad->calle->name ?? null;
            $propiedad->zona->name ?? null;
            $propiedad->tipoInmueble->inmueble ?? null;
            $propiedad->precioActual ?? null;
        }

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
        /* Log::info($resultado); */
        return response()->json($resultado);
    }

    public function MuestraPropiedad(Request $request)
    {
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

        // Llamar al método manualmente
        $foliosActivos = $propiedad ? $propiedad->buscarCasa() : [];
        $contratoMasReciente = $propiedad ? $propiedad->buscarContratoMasReciente() : [];

        // Obtener el detalle del contrato más alto
        $detalleContrato = null;
        if (!empty($contratoMasReciente) && isset($contratoMasReciente['id_contrato_cabecera'])) {
            $detalleContrato = $propiedad->buscarDetalleContratoMasAlto($contratoMasReciente['id_contrato_cabecera']);
        }

        // Ver qué retorna
        //Log::info('Folios activos encontrados: ', $foliosActivos);
        //Log::info('Contrato más reciente: ', $contratoMasReciente);
        //Log::info('Detalle del contrato: ', $detalleContrato ?: []);

        // Convertir a array y eliminar valores null
        $propiedadArray = $propiedad->toArray();
        $propiedadArray['buscarFolioActivo'] = $foliosActivos;
        $propiedadArray['buscarContratoMasReciente'] = $contratoMasReciente; // Agregar resultado manualmente
        $propiedadArray['detalleContrato'] = $detalleContrato;

        $propiedadFiltrada = array_filter($propiedadArray, function ($value) {
            return $value !== null;
        });

        return response()->json($propiedadFiltrada);
    }

    public function actualizarPropiedad(Request $request)
    {
        try {
            Log::info('Entrando a actualizarPropiedad');
            Log::info($request->all());

            $propiedad = Propiedad::find($request->id);
            if (!$propiedad) {
                return response()->json(['message' => 'Propiedad no encontrada'], 404);
            }

        // Decodificar las comodidades si vienen en formato JSON y limpiar el array
        $comodidades = [];
        if ($request->has('comodidades')) {
            $comodidades = $this->cleanArray(json_decode($request->comodidades, true) ?? []);
        }

        $descripcion = [];
        if ($request->has('descripcion')) {
            $descripcion = $this->cleanArray(json_decode($request->descripcion, true) ?? []);
        }

        $venta = [];
        if ($request->has('venta')) {
            $venta = $this->cleanArray(json_decode($request->venta, true) ?? []);
        }

        $alquiler = [];
        if ($request->has('alquiler')) {
            $alquiler = $this->cleanArray(json_decode($request->alquiler, true) ?? []);
        }

        $condicion_alquiler = [];
        if ($request->has('condicion_alquiler')) {
            $condicion_alquiler = $this->cleanArray(json_decode($request->condicion_alquiler, true) ?? []);
        }

        $usuario_id = $request->id_usuario;

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
            //Actualizar compo descripcion
            'descipcion_propiedad'        => $descripcion['texto'] ?? null,
            //Actualizar campo de venta
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
            //Actualizar campo de alquiler
            'cod_alquiler'                => $alquiler['cod_alquiler'] ?? null,
            'id_estado_alquiler'          => $alquiler['estado_alquiler'] ?? null,
            'autorizacion_alquiler'       => $alquiler['autorizacion_alquiler'] ?? null,
            'fecha_autorizacion_alquiler' => $alquiler['fecha_autorizacion_alquiler'] ?? null,
            'exclusividad_alquiler'       => $alquiler['exclusividad_alquiler'] ?? null,
            'clausula_de_venta'           => $alquiler['clausula_de_venta'] ?? null,
            'tiempo_clausula'             => $alquiler['tiempo_clausula'] ?? null,
            'alquiler_fecha_alta'         => $alquiler['alquiler_fecha_alta'] ?? null,
            'mascota'                     => $alquiler['mascota'] ?? null,
            //Condicion alquiler
            'condicion'                   => $condicion_alquiler['condicion'] ?? null

        ]);


        (new TasacionService())->crearDesdeRequest($venta, $propiedad->id);
        (new PrecioService())->crearDesdeRequest($venta, $alquiler, $propiedad->id);

        if ($request->has('fotos_modificadas')) {
            $fotos_modificadas = $this->cleanArray(json_decode($request->fotos_modificadas, true));
            (new PropiedadMediaService())->modificarFoto($fotos_modificadas);
        }
        if ($request->has('fotos_eliminadas')) {
            $fotos_eliminadas = json_decode($request->fotos_eliminadas, true);
            (new PropiedadMediaService())->eliminarFoto($fotos_eliminadas);
        }
        if ($request->has('fotos_nuevas_data')) {
            (new PropiedadMediaService())->subirdesdeUpdate($request, $propiedad->id);
        }
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

         $folios = [
                1 => $alquiler['FCentral'] ?? null,
                2 => $alquiler['FCandioti'] ?? null,
                3 => $alquiler['FTribunales'] ?? null,
            ];

            //dd('esto es un dd de folio', $folios);
            $propiedadId = $propiedad->id;

            if ($alquiler['FCentral'] != '-' || $alquiler['FCandioti'] != '-' || $alquiler['FTribunales'] != '-') {
                $this->empresaPropiedadService->actualizarFolioExistente(
                    $propiedadId,
                    $folios
                );
            }

        return response()->json(['message' => 'Propiedad actualizada correctamente']);

        } catch (\Exception $e) {
            Log::error("Error al actualizar propiedad: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function descargarFotos($id)
    {
        try {
            Log::info('Entrando a descargar fotos', ['propiedad_id' => $id]);

            $fotos = Foto::where('propiedad_id', $id)->get();

            if ($fotos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron fotos para esta propiedad.'
                ], 404);
            }

            $propiedad = $this->propiedadService->obtenerPropiedadesPorId($id);
            $calle = Calle::find($propiedad->id_calle);
            $numero = $propiedad->numero_calle;

            // Limpiar nombre del archivo para evitar caracteres inválidos
            $calleName = preg_replace('/[^a-zA-Z0-9\s]/', '', $calle->name);
            $zipFileName = trim($calleName) . '-' . $numero . '.zip';

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
                        Log::warning('Archivo no encontrado', ['path' => $filePath]);
                    }
                }

                if ($filesAdded > 0) {
                    Log::info("ZIP creado exitosamente", [
                        'archivos_agregados' => $filesAdded,
                        'archivos_no_encontrados' => $filesNotFound
                    ]);
                    $zip->finish();
                } else {
                    Log::warning('No se encontraron archivos físicos para comprimir');
                    echo "No se encontraron archivos físicos para comprimir.";
                }
            }, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al descargar fotos', [
                'propiedad_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la descarga de fotos.'
            ], 500);
        }
    }

    /**
     * Actualiza las observaciones de una propiedad, registrando una novedad
     * ya sea para venta o alquiler según el formulario enviado.
     *
     * Inserta un registro en la tabla observaciones_propiedades dentro
     * de una transacción para asegurar consistencia.
     *
     * @param \Illuminate\Http\Request $request       Datos enviados por el formulario.
     * @param string|int $propiedad_id                ID de la propiedad a actualizar.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $propiedad_id)
    {
        DB::beginTransaction(); // Iniciar transacción

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

            DB::commit(); // Confirmar la transacción
            return redirect()->back()->with('success', 'Novedad cargada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            return redirect()->back()->with('error', 'Error al guardar la novedad.');
        }
    }

    public function guardarNovedad(Request $request)
    {
        Log::info($request->all());
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
    }


    public function destroy(string $id)
    {
        //
    }

    public function guardarCambio(Request $request)
    {
        // Validar que el campo y el valor se han enviado correctamente
        $request->validate([
            'campo' => 'required|string',
            'valor' => 'required|string',
        ]);

        DB::beginTransaction(); // Iniciar transacción

        try {
            // Almacenar el cambio en la sesión
            session()->put($request->campo, $request->valor);
            session()->save();

            DB::commit(); // Confirmar la "transacción"
            // Responder con éxito
            return response()->json([
                'status' => 'success',
                'message' => 'Cambio guardado correctamente en la sesión.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios en caso de error
            // Responder con error
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar el cambio.'
            ], 500);
        }
    }


    public function search(Request $request)
    {
        $codigo = $request->query('codigo', '');
        $calle  = $request->query('calle', '');
        $sector = $request->query('sector_asesor', '');



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
    }





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
