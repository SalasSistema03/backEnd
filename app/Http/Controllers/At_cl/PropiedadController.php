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
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

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
            'observaciones_llave' => $request->observaciones_llave,
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
            foreach ($propietario as $propietario_item) {
                if (isset($propietario_item['persona']['id'])) {
                    $this->propiedad_padronService->vincular($propiedad_creada->id, $propietario_item['persona']['id']);
                }
            }
        }
    }

   

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
    

    /**
     * Muestra el formulario de creación de una propiedad
     *
     * Verifica si el usuario tiene permisos para acceder a la vista de carga de propiedades.
     * Si no cuenta con acceso, redirige al inicio con un mensaje de error. En caso contrario,
     * retorna la vista con los datos necesarios para cargar una nueva propiedad.
     *
     * @param  Request $request instancia de la solicitud HTTP recibida
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse vista de carga de propiedad o redirección si no hay permisos
     */
    public function create(Request $request)
    {
        // Nombre de la vista correspondiente en la base de datos
        $vistaNombre = 'propiedadCargar';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        $usuariosTotales = Usuario::all();
        $usuarioAsesor = Usuario_sector::where('venta', 'S')->get('id_usuario');
        foreach ($usuarioAsesor as $usuarioTot) {
            $username = Usuario::where('id', $usuarioTot->id_usuario)->get('username');
            $usuarioTot->username = $username;
        }

        return view('atencionAlCliente.propiedad.cargarPropiedad', [
            'zona' => $this->zona,
            'calle' => $this->calle,
            'barrio' => $this->barrio,
            'estado_alquileres' => $this->estado_alquileres,
            'estado_general' => $this->estado_general,
            'estado_venta' => $this->estado_venta,
            'localidad' => $this->localidad,
            'tipo_inmueble' => $this->tipo_inmueble,
            'provincia' => $this->provincia,
            'padron' => $this->padron,
            'usuario' => $this->usuario,
            'usuariosTotales' => $usuariosTotales,
            'usuarioAsesor' => $usuarioAsesor,

        ]);
    }




    /**
     * Muestra la vista detallada de una propiedad
     *
     * Obtiene los datos completos de una propiedad según su ID, junto con información
     * relacionada como fotos, documentos, videos, propietarios, precios, contratos
     * y accesos del usuario a diferentes secciones. Además, verifica si el usuario
     * tiene permiso para acceder a la vista antes de mostrarla.
     *
     * @param  string $id identificador único de la propiedad a mostrar
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse vista con la información de la propiedad o redirección si no hay permisos
     * @access public
     */
    public function show(string $id)
    {


        // Definimos un array con los nombres de los botones
        $btnNombres = [

            'propietario',
            'informacion_venta',
            'informacion_alquiler',
            'modificar'
        ];
        // Inicializamos un array vacío para almacenar los accesos
        $accesos = [];
        // Recorremos cada nombre de botón
        foreach ($btnNombres as $btnNombre) {
            // Verificamos si el usuario tiene acceso a cada botón y almacenamos el resultado en el array de accesos
            $accesos[$btnNombre] = $this->accessService->tieneAcceso($btnNombre);
        }

        // Asignamos el acceso a 'propietario' a una variable
        $tieneAccesoPropietario = $accesos['propietario'];
        // Asignamos el acceso a 'InformacionVenta' a una variable
        $tieneAccesoInformacionVenta = $accesos['informacion_venta'];
        // Asignamos el acceso a 'InformacionAlquiler' a una variable
        $tieneAccesoInformacionAlquiler = $accesos['informacion_alquiler'];
        // Asignamos el acceso a 'modificar' a una propiedad
        $tieneAccesoModificar = $accesos['modificar'];
        // Definimos el nombre de la vista
        $vistaNombre = 'propiedad';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }


        $usuario = $this->usuario;
        $propiedad = $this->propiedadService->obtenerPropiedadConId($id);
        $empresaPropiedad = $this->propiedadService->obtenerEmpresaPropiedad($id);
        $contratoMasNuevo = $this->propiedadService->obtenerContratoMasReciente($id);
        $idCasas = $contratoMasNuevo->id_casa ?? null;
        $folio = $contratoMasNuevo->folio ?? null;
        $vencimiento_contratos = $contratoMasNuevo->vencimiento_contrato ?? null;
        $inicio_contrato = $contratoMasNuevo->inicio_contrato ?? null;
        $propietarios = $this->propiedadService->obtenerPropietarios($id);
        $ultimoPrecio = $this->precioService->obtenerUltimoPrecio($id);
        $precio = $ultimoPrecio;
        $observaciones_propiedades_venta = $this->observacionesPropiedadesService->obtenerObservacionesVenta($id);
        $observaciones_propiedades_alquiler = $this->observacionesPropiedadesService->obtenerObservacionesAlquiler($id);
        $fotos = $this->fotoService->obtenerFotos($id);
        $documentos = $this->documentacionService->obtenerDocumento($id);
        $videos = $this->videoService->obtenerVideos($id);
        $historialFecha = $this->historialFechasService->obtenerHistorialFecha($id);
        $tasacion = $this->tasacionService->obtenerUltimaTasacion($id);
        $padrones = $this->propiedad_padronService->obtenerPropietarios($id);
        $alquiler = $this->propiedadService->obtenerAlquiler($idCasas, $folio);
        $username_asesor = Usuario::where('id', $propiedad->asesor)->first()->username ?? null;

        return view(
            'atencionAlCliente.propiedad.propiedad',
            compact(
                'propiedad',
                'historialFecha',
                'vencimiento_contratos',
                'inicio_contrato',
                'observaciones_propiedades_venta',
                'observaciones_propiedades_alquiler',
                'precio',
                'fotos',
                'propietarios',
                'documentos',
                'tasacion',
                'usuario',
                'tieneAccesoPropietario',
                'tieneAccesoInformacionVenta',
                'tieneAccesoInformacionAlquiler',
                'tieneAccesoModificar',
                'padrones',
                'videos',
                'alquiler',
                'empresaPropiedad',
                'username_asesor',
                'folio'

            )
        );
    }

    /**
     * Muestra el formulario para editar una propiedad
     *
     * Verifica si el usuario tiene permiso para acceder a la vista de edición y, 
     * si está autorizado, obtiene toda la información necesaria relacionada a la 
     * propiedad, incluyendo datos generales, precios, tasación, fotos y padrones. 
     * Luego retorna la vista con todos los datos requeridos para permitir la edición.
     *
     * @param  string $id identificador único de la propiedad a editar
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse vista del formulario de edición o redirección si no hay permisos
     * @access public
     */
    public function edit(string $id)
    {

        $vistaNombre = 'propiedadEditar';

        // Crear una instancia del servicio de permisos
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);

        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        // Obtener todos los padrones con nombre y apellido combinados
        $buscarPropietarios = DB::table('padron')->select('id', DB::raw("CONCAT(nombre, ' ', apellido) as name"))->get();

        $historialEstados = $this->propiedadService->obtenerUltimoHistorialEstadosVenta($id);
        $historialEstadosAlquiler = $this->propiedadService->obtenerUltimoHistorialEstadosAlquiler($id);
        $empresaPropiedad = $this->propiedadService->obtenerEmpresaPropiedad($id);

        $propiedad = $this->propiedadService->obtenerPropiedadConId($id);

        $precio = $this->precioService->obtenerUltimoPrecio($id);
        $tasacion = $this->tasacionService->obtenerUltimaTasacion($id);
        $fotos = $this->fotoService->obtenerFotos($id);
        $padrones = $this->propiedad_padronService->obtenerPropietarios($id);
        $usuariosTotales = Usuario::all();

        $usuarioAsesor = Usuario_sector::where('venta', 'S')->get('id_usuario');
        foreach ($usuarioAsesor as $usuarioTot) {
            $username = Usuario::where('id', $usuarioTot->id_usuario)->get('username');
            $usuarioTot->username = $username;
        }




        return view('atencionAlCliente.propiedad.editarPropiedad', [
            'barrio' => $this->barrio,
            'zona' => $this->zona,
            'localidad' => $this->localidad,
            'provincia' => $this->provincia,
            'tipo_inmueble' => $this->tipo_inmueble,
            'estado_general' => $this->estado_general,
            'estado_venta' => $this->estado_venta,
            'estado_alquileres' => $this->estado_alquileres,
            'padron' => $this->padron,
            'calle' => $this->calle,
            'propiedad' => $propiedad,
            'precio' => $precio,
            'tasacion' => $tasacion,
            'fotos' => $fotos,
            /* 'propietarios' => $propietarios, */
            'buscarPropietarios' => $buscarPropietarios,
            'padrones' => $padrones,
            'historialEstados' => $historialEstados,
            'historialEstadosAlquiler' => $historialEstadosAlquiler,
            'usuariosTotales' => $usuariosTotales,
            'empresaPropiedad' => $empresaPropiedad,
            'usuarioAsesor' => $usuarioAsesor,
        ]);
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


    public function destroy(string $id)
    {
        //
    }
    public function updatedatos(Request $request, $id)
    {

        //Se llama al servicio de historial de estados que esta en propiedadService

        $this->propiedadService->guardarHistorialEstadosSerbive(
            $id,
            $request->input('estado_venta'),
            $request->input('estado_alquiler'),
            $request->input('descripcion_estado_alquiler'),
            $request->input('descripcion_estado_venta'),
            $request->input('fecha_baja_temporal_alquiler'),
            $request->input('fecha_baja_temporal_venta')
        );


        // Guardar los datos esenciales antes de limpiar
        $datosEsenciales = [
            'usuario' => session('usuario'),
            'usuario_id' => session('usuario_id'),
            'usuario_nombre' => session('usuario_nombre'),
            'usuario_interno' => session('usuario_interno'),
            'admin' => session('admin'),
            'last_activity' => session('last_activity')
        ];

        DB::beginTransaction(); // Iniciar la transacción

        try {
            $usuario = $this->usuario->id;
            $propiedad = Propiedad::findOrFail($id);
            $propiedadAntesUpdate = Empresas_propiedades::where('propiedad_id', $propiedad->id)->get();
            //dd($propiedadAntesUpdate);

            $propiedad->update([
                'id_calle' => $request->calle,
                'numero_calle' => $request->numero_calle,
                'piso'  => $request->piso,
                'departamento' => $request->depto,
                'id_zona' => $request->zona,
                'id_provincia' => $request->provincia,
                'llave' => $request->llave,
                'comentario_llave' => $request->observacion_llave,
                'cartel' => $request->cartel,
                'comentario_cartel' => $request->observacion_cartel,
                'id_inmueble' => $request->tipo_inmueble,
                'id_estado_general' => $request->estado_general,
                'cantidad_dormitorios' => $request->dormitorios,
                'banios' => $request->banios,
                'mLote' => $request->m_Lote,
                'mCubiertos' => $request->m_Cubiertos,
                'cochera' => $request->cochera,
                'numero_cochera' => $request->numero_cochera,
                'asfalto' => $request->asfalto,
                'gas' => $request->gas,
                'cloaca' => $request->cloaca,
                'agua' => $request->agua,
                'descipcion_propiedad' => $request->descipcion_propiedad,
                'cod_venta' => $request->cod_venta,
                'id_estado_venta' => $request->estado_venta,
                'id_estado_alquiler' => $request->estado_alquiler,
                'monto_venta' => $request->monto_venta,
                'comparte_venta' => $request->comparte_venta,
                'autorizacion_venta' => $request->autorizacion_venta,
                'fecha_autorizacion_venta' => $request->fecha_autorizacion_venta,
                'exclusividad_venta' => $request->exclusividad_venta,
                'condicionado_venta' => $request->condicionado_venta,
                'cod_alquiler' => $request->cod_alquiler,
                'estado_alquiler' => $request->estado_alquiler,
                'moneda_alquiler' => $request->moneda_alquiler,
                'monto_alquiler' => $request->monto_alquiler,
                'autorizacion_alquiler' => $request->autorizacion_alquiler,
                'fecha_autorizacion_alquiler' => $request->fecha_autorizacion_alquiler,
                'exclusividad_alquiler' => $request->exclusividad_alquiler,
                'clausula_de_venta' => $request->clausula_de_venta,
                'tiempo_clausula' => $request->tiempo_clausula,
                'last_modified_by' => $usuario,
                'ph' => $request->ph,
                'condicion' => $request->condicion,
                'comentario_autorizacion' => $request->comentario_autorizacion,
                'venta_fecha_alta' => $request->venta_fecha_alta,
                'alquiler_fecha_alta' => $request->alquiler_fecha_alta,
                'zona_prop' => $request->zona_prop,
                'flyer' => $request->flyer,
                'reel' => $request->reel,
                'web' => $request->web,
                'captador_int' => $request->captador_int,
                'asesor' => $request->asesor
            ]);

            /* ---------------------------------------------------------------------------------------------------------------- */
            //Si se envio observaciones se actualiza el padron
            if ($request->filled('observaciones')) {
                //Actualizamos el padron
                DB::table('propiedades_padron')
                    ->where('propiedad_id', $request->input('propiedad_id'))
                    ->where('padron_id', $request->input('padron_id'))
                    ->update([
                        'observaciones_baja' => $request->input('observaciones'),
                        'baja' => 'si',
                        'fecha_baja' => now(),
                    ]);
            }
            /* ---------------------------------------------------------------------------------------------------------------- */
            //Creamos un array para la tasacion
            $precioTasacion = [];
            $ultimaTasacion  = $this->tasacionService->obtenerUltimaTasacion($id);

            if ($request->filled('monto_tasacion') || $request->filled('moneda_venta_tasacion')) {

                $tasacion_dolar_venta = $ultimaTasacion->tasacion_dolar_venta ?? null;
                $tasacion_pesos_venta = $ultimaTasacion->tasacion_pesos_venta ?? null;
                $monto_tasacion = (float) $request->monto_tasacion;

                if ($monto_tasacion != $tasacion_dolar_venta && $monto_tasacion != $tasacion_pesos_venta) {
                    if ($request->moneda_venta_tasacion == '1') {
                        $precioTasacion = array_merge($precioTasacion, [
                            'tasacion_pesos_venta' => $request->monto_tasacion,
                            'tasacion_dolar_venta' => null, // No se usa
                            'fecha_tasacion' => $request->fecha_tasacion_venta,
                            'moneda' => '1',
                            'propiedad_id' => $propiedad->id,
                        ]);
                    } else {
                        $precioTasacion = array_merge($precioTasacion, [
                            'tasacion_pesos_venta' => null, // No se usa
                            'tasacion_dolar_venta' => $request->monto_tasacion,
                            'fecha_tasacion' => $request->fecha_tasacion_venta,
                            'moneda' => '2',
                            'propiedad_id' => $propiedad->id,
                        ]);
                    }
                }
            }

            if (!empty($precioTasacion)) {
                Tasacion::create($precioTasacion);
            }

            /* --------------------------------------------------------------------------------------------------------------------------------- */
            $folios = [
                1 => $request->FCentral,
                2 => $request->FCandioti,
                3 => $request->FTribunales,
            ];

            //dd($folios);
            $propiedadId = $propiedad->id;

            if ($request->FCentral != '-' || $request->FCandioti != '-' || $request->FTribunales != '-') {
                $this->empresaPropiedadService->actualizarFolioExistente(
                    $propiedadId,
                    $folios
                );
            }


            /*---------------------------------------------------------------------------------------------------------------------------------  */
            //Buscamos el ultimos precio
            $ultimoPrecio = $this->precioService->obtenerUltimoPrecio($id);
            //Acemos una replica
            $replicaUltimoPrecio = $ultimoPrecio->replicate();
            //Modifacamos el valor  que se modifico
            if ($request->filled('moneda_venta') && $request->filled('monto_venta')) {
                if ($request->moneda_venta == '1') {
                    $replicaUltimoPrecio->moneda_venta_pesos = $request->monto_venta;
                } elseif ($request->moneda_venta == '2') {
                    $replicaUltimoPrecio->moneda_venta_dolar = $request->monto_venta;
                }
            }
            if ($request->filled('moneda_alquiler') && $request->filled('monto_alquiler')) {
                if ($request->moneda_alquiler == '1') {
                    $replicaUltimoPrecio->moneda_alquiler_pesos = $request->monto_alquiler;
                } elseif ($request->moneda_alquiler == '2') {
                    $replicaUltimoPrecio->moneda_alquiler_dolar = $request->monto_alquiler;
                }
            }
            //Guardamos el precio modificado
            $replicaUltimoPrecio->save();

            /* ----------------------------------------------------------------------------------------------------------------- */
            //Borramos datos que nos hace repetir la informacion en los modales de venta y alquiler
            session()->forget('resultado');
            session()->forget('monto_alquiler');
            session()->forget('monto_venta');

            // Limpiar toda la sesión excepto los tokens y datos del sistema
            $sistemaData = [
                '_token' => session('_token'),
                '_previous' => session('_previous'),
                '_flash' => session('_flash')
            ];

            // Limpiar sesión y restaurar datos esenciales
            session()->flush();
            session($sistemaData);
            session($datosEsenciales);

            DB::commit(); // Confirmar la transacción
            return redirect()->route('propiedad.show', $id)->with('success', 'Datos actualizados correctamente.');
        } catch (QueryException $e) {
            DB::rollBack();
            // Verifica si el error es por clave única duplicada
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'cod_venta')) {
                return redirect()->back()->withInput()->with('error', 'El código de venta ya está registrado para otra propiedad.');
            }
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'cod_alquiler')) {
                return redirect()->back()->withInput()->with('error', 'El código de alquiler ya está registrado para otra propiedad.');
            }
            // Otros errores de base de datos
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar los datos')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar los datos')->with('error', $e->getMessage());
        }
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


    public function darDeBaja(Request $request)
    {
        // Validar que el campo propiedad_id y padron_id existen
        $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'padron_id' => 'required|exists:padron,id',
        ]);

        $propiedadId = $request->input('propiedad_id');
        $padronId = $request->input('padron_id');

        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Actualiza solo el registro específico en la tabla intermedia
            $fechaBaja = now();
            $updated = DB::table('propiedades_padron')
                ->where('propiedad_id', $propiedadId)
                ->where('padron_id', $padronId)
                ->update([
                    'baja' => 'si',
                    'fecha_baja' => $fechaBaja,
                ]);

            if ($updated) {
                DB::commit(); // Confirmar la transacción si fue exitosa
                return response()->json([
                    'success' => true,
                    'fecha_baja' => $fechaBaja->format('Y-m-d'),
                    'message' => 'Propietario dado de baja correctamente.',
                ]);
            } else {
                DB::rollBack(); // Revertir si no se pudo actualizar
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar el registro.',
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al dar de baja al propietario.',
            ], 500);
        }
    }


    public function darDeAlta(Request $request)
    {
        // Validar que el campo propiedad_id y padron_id existen
        $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'padron_id' => 'required|exists:padron,id',
        ]);

        $propiedadId = $request->input('propiedad_id');
        $padronId = $request->input('padron_id');

        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Actualiza solo el registro específico en la tabla intermedia
            $fechaAlta = now();
            $updated = DB::table('propiedades_padron')
                ->where('propiedad_id', $propiedadId)
                ->where('padron_id', $padronId)
                ->update([
                    'baja' => 'no',
                    'fecha_baja' => null,
                ]);

            if ($updated) {
                DB::commit(); // Confirmar la transacción si fue exitosa
                return response()->json([
                    'success' => true,
                    'fecha_alta' => $fechaAlta->format('Y-m-d'),
                    'message' => 'Propietario dado de alta correctamente.',
                ]);
            } else {
                DB::rollBack(); // Revertir si no se pudo actualizar
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar el registro.',
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al dar de alta al propietario.',
            ], 500);
        }
    }


    public function asignarPersona(Request $request)
    {
        // Validar que los IDs existan en sus respectivas tablas
        $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'persona_id' => 'required|exists:padron,id',
        ]);

        $propiedadId = $request->input('propiedad_id');
        $personaId = $request->input('persona_id');

        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Verificar si la persona ya está relacionada con la propiedad
            $existeRelacion = DB::table('propiedades_padron')
                ->where('propiedad_id', $propiedadId)
                ->where('padron_id', $personaId)
                ->exists();

            if ($existeRelacion) {
                DB::rollBack(); // Cancelar si ya existe la relación
                return response()->json([
                    'success' => false,
                    'message' => 'La persona ya está relacionada con esta propiedad.',
                ], 400);
            }

            // Insertar la persona en la tabla intermedia
            $asignado = DB::table('propiedades_padron')->insert([
                'propiedad_id' => $propiedadId,
                'padron_id' => $personaId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($asignado) {
                DB::commit(); // Confirmar si se insertó correctamente
                return response()->json([
                    'success' => true,
                    'message' => 'Persona asignada correctamente a la propiedad.',
                ]);
            } else {
                DB::rollBack(); // Revertir si falló la inserción
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo asignar la persona.',
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir en caso de excepción
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al asignar la persona.',
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


    public function descargarFotos($id)
    {
        $fotos = Foto::where('propiedad_id', $id)->get();
        $propiedad = $this->propiedadService->obtenerPropiedadesPorId($id);
        $calle = Calle::find($propiedad->id_calle);
        $numero = $propiedad->numero_calle;

        if ($fotos->isEmpty()) {
            return back()->with('error', 'No se encontraron fotos para esta propiedad.');
        }

        $zipFileName = $calle->name . '-' . $numero . '.zip';

        // Usamos la librería ZipStream para enviar el archivo directamente
        return response()->streamDownload(function () use ($fotos) {
            // Inicializar el buffer de salida
            if (ob_get_level() == 0) {
                ob_start();
            }

            $zip = new \ZipStream\ZipStream(
                outputName: 'fotos.zip',
                sendHttpHeaders: false
            );

            $basePath = '\\\\10.10.10.151\\Compartida\\PROPIEDADES';
            $filesAdded = false;

            foreach ($fotos as $foto) {
                $imagePath = str_replace('/imagenes', '', $foto->url);
                $filePath = $basePath . str_replace('/', '\\', $imagePath);

                if (file_exists($filePath)) {
                    $zip->addFileFromPath(basename($filePath), $filePath);
                    $filesAdded = true;
                }
            }

            if ($filesAdded) {
                $zip->finish();
            } else {
                echo "No se encontraron archivos para comprimir.";
            }

            ob_end_flush();
        }, $zipFileName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"'
        ]);
    }

    /**
     * Función recursiva para limpiar valores vacíos en arrays
     *
     * @param mixed $data Datos a limpiar
     * @return mixed Datos limpios
     */
    private function cleanArray($data)
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
