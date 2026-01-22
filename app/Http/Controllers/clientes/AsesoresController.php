<?php

namespace App\Http\Controllers\clientes;


use Illuminate\Http\Request;
use App\Models\At_cl\Calle;
use App\Models\cliente\Usuario_sector;
use App\Models\cliente\Clientes;
use App\Models\cliente\CriterioBusquedaVenta;
use App\Models\cliente\HistorialCriteriosConversacion;
use Illuminate\Support\Facades\DB;
use App\Models\cliente\HistorialCodMuestra;
use App\Models\At_cl\tipo_inmueble;
use App\Models\At_cl\Zona;
use App\Models\At_cl\Usuario;
use App\Services\At_cl\PermitirAccesoPropiedadService;
use App\Models\cliente\HistorialCodOfrecimiento;
use App\Models\cliente\HistorialCodigoConsulta;
use App\Services\At_cl\PropiedadService;

class AsesoresController
{
    public $propiedadService;
    protected $accessService;
    protected $usuario_id;
    protected $usuario;


    public function __construct(PropiedadService $propiedadService)
    {
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->usuario = Usuario::find($this->usuario_id);
        $this->propiedadService = $propiedadService;
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
    }

    public function index()
    {
        $vistaNombre = 'asesores';

        // Verificar permisos (mantener lógica original)
        $permisoService = new PermitirAccesoPropiedadService($this->usuario->id);
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }

        $usuario_id = session()->get('usuario_id');
        $usuario = session('usuario');
        $usuario_nombre = $usuario ? $usuario->username : null;

        // OPTIMIZACIÓN 1: Verificar sector una sola vez
        $usuario_sector = Usuario_sector::where('id_usuario', $usuario_id)->first();
        if (!$usuario_sector) {
            return redirect()->route('home')->with('error', 'Acceso denegado.');
        }

        try {
            // OPTIMIZACIÓN 2A: Consulta principal sin tipo_inmueble (diferentes conexiones)
            $clientesConCriterios = DB::connection('mysql5')
                ->table('clientes as c')
                ->leftJoin('criterio_busqueda_venta as cbv', 'c.id_cliente', '=', 'cbv.id_cliente')
                ->where('c.id_asesor_venta', $usuario_sector->id_usuario)
                ->select([
                    'c.*',
                    'cbv.id_criterio_venta',
                    'cbv.fecha_criterio_venta',
                    'cbv.estado_criterio_venta',
                    'cbv.id_categoria',
                    'cbv.id_tipo_inmueble',
                    'cbv.id_zona', // Asegúrate de incluir este campo
                    'cbv.cant_dormitorios', // Añade esta línea
                    'cbv.cochera',
                    'cbv.observaciones_criterio_venta',
                    'cbv.precio_hasta',
                ])
                ->orderBy('c.id_cliente')
                ->orderBy('cbv.fecha_criterio_venta', 'ASC')
                ->get();

            // OPTIMIZACIÓN 2B: Obtener tipos de inmueble por separado de la conexión correcta
            $tipos_inmueble_ids = $clientesConCriterios->pluck('id_tipo_inmueble')->filter()->unique();
            $tipos_inmueble_map = DB::connection('mysql')
                ->table('tipo_inmueble')
                ->whereIn('id', $tipos_inmueble_ids)
                ->get()
                ->keyBy('id');

            if ($clientesConCriterios->isEmpty()) {
                return redirect()->route('home')->with('error', 'No se encontraron clientes asignados al sector del usuario.');
            }

            // OPTIMIZACIÓN 3: Procesamiento en memoria en lugar de múltiples consultas
            $clientes = collect();
            $criterios_venta = collect();
            $criterios_mas_antiguos_por_cliente = collect();

            $clientesAgrupados = $clientesConCriterios->groupBy('id_cliente');


            foreach ($clientesAgrupados as $id_cliente => $registros) {
                $primerRegistro = $registros->first();

                // Crear objeto cliente
                $cliente = (object) [
                    'id_cliente' => $primerRegistro->id_cliente,
                    'nombre' => $primerRegistro->nombre,
                    'telefono' => $primerRegistro->telefono,
                    'observaciones' => $primerRegistro->observaciones,
                    'ingreso' => $primerRegistro->ingreso,
                    'pertenece_a_inmobiliaria' => $primerRegistro->pertenece_a_inmobiliaria,
                    'nombre_de_inmobiliaria' => $primerRegistro->nombre_de_inmobiliaria,
                    'id_asesor_venta' => $primerRegistro->id_asesor_venta,
                    'usuario_id' => $primerRegistro->usuario_id,
                ];

                $clientes->push($cliente);

                // Procesar criterios del cliente
                $criteriosCliente = $registros->filter(function ($r) {
                    return !is_null($r->id_criterio_venta);
                });

                if ($criteriosCliente->isNotEmpty()) {
                    foreach ($criteriosCliente as $criterio) {
                        $criterios_venta->push((object) [
                            'id_criterio_venta' => $criterio->id_criterio_venta,
                            'id_cliente' => $criterio->id_cliente,
                            'fecha_criterio_venta' => $criterio->fecha_criterio_venta,
                            'estado_criterio_venta' => $criterio->estado_criterio_venta,
                            'id_categoria' => $criterio->id_categoria,
                            'id_tipo_inmueble' => $criterio->id_tipo_inmueble,
                            'id_zona' => $criterio->id_zona ?? null, // Asegúrate de incluir esta línea
                            'cant_dormitorios' => $criterio->cant_dormitorios ?? null, // Añade esta línea
                            'cochera' => $criterio->cochera ?? null, // Añade esta línea
                            'observaciones_criterio_venta' => $criterio->observaciones_criterio_venta ?? null,
                            'precio_hasta' => $criterio->precio_hasta ?? null, // Añade esta línea
                        ]);
                    }

                    // Criterio más antiguo por cliente
                    $criterioMasAntiguo = $criteriosCliente->sortBy('fecha_criterio_venta')->first();
                    $criterios_mas_antiguos_por_cliente->put($id_cliente, $criterioMasAntiguo);
                }
            }

            // OPTIMIZACIÓN 4: Consultas para historiales con conexión correcta
            $faltaDevolucion = collect();

            // Asumiendo que los historiales están en mysql5 como los criterios
            $historialIds = DB::connection('mysql5')
                ->table('historial_cod_muestra')
                ->whereNull('devolucion')
                ->pluck('id_criterio_venta')
                ->merge(
                    DB::connection('mysql5')
                        ->table('historial_cod_ofrecimiento')
                        ->whereNull('devolucion')
                        ->pluck('id_criterio_venta')
                )
                ->merge(
                    DB::connection('mysql5')
                        ->table('historial_cod_consulta')
                        ->whereNull('devolucion')
                        ->pluck('id_criterio_venta')
                )
                ->unique();

            $faltaDevolucion = $historialIds->toArray();

            // OPTIMIZACIÓN 5: Calcular clientes con devolución pendiente en memoria
            $clientesConDevolucionPendiente = $criterios_venta
                ->whereIn('id_criterio_venta', $faltaDevolucion)
                ->pluck('id_cliente')
                ->unique()
                ->toArray();

            // Marcar clientes con devolución pendiente
            $clientes->each(function ($cliente) use ($clientesConDevolucionPendiente) {
                $cliente->faltaDevolucion = in_array($cliente->id_cliente, $clientesConDevolucionPendiente);
            });

            // OPTIMIZACIÓN 6: Ordenamiento optimizado
            $clientes_ordenados = $clientes->sortBy(function ($cliente) use ($criterios_venta) {
                $criterios = $criterios_venta->where('id_cliente', $cliente->id_cliente);

                if ($criterios->isEmpty()) {
                    return [0, PHP_INT_MAX];
                }

                // Filtros optimizados usando colecciones
                $criterios_activo_null = $criterios->where('estado_criterio_venta', 'Activo')
                    ->whereNull('id_categoria');

                if ($criterios_activo_null->count() > 0) {
                    $fecha = $criterios_activo_null->sortBy('fecha_criterio_venta')->first()->fecha_criterio_venta ?? null;
                    return [1, strtotime($fecha) ?: PHP_INT_MAX];
                }

                $criterios_activo_categoria = $criterios->where('estado_criterio_venta', 'Activo')
                    ->whereNotNull('id_categoria');

                if ($criterios_activo_categoria->count() > 0) {
                    $prioridad_categoria = ['Potable' => 2, 'Medio' => 3, 'No Potable' => 4];
                    $criterio_prioritario = $criterios_activo_categoria
                        ->sortBy(function ($c) use ($prioridad_categoria) {
                            return $prioridad_categoria[$c->id_categoria] ?? 999;
                        })
                        ->first();

                    $prioridad_principal = $prioridad_categoria[$criterio_prioritario->id_categoria] ?? 999;
                    $fecha = $criterio_prioritario->fecha_criterio_venta ?? null;
                    return [$prioridad_principal, strtotime($fecha) ?: PHP_INT_MAX];
                }

                $criterios_finalizados = $criterios->where('estado_criterio_venta', 'Finalizado');
                if ($criterios_finalizados->count() > 0) {
                    $fecha = $criterios_finalizados->sortBy('fecha_criterio_venta')->first()->fecha_criterio_venta ?? null;
                    return [5, strtotime($fecha) ?: PHP_INT_MAX];
                }

                $fecha = $criterios->sortBy('fecha_criterio_venta')->first()->fecha_criterio_venta ?? null;
                return [6, strtotime($fecha) ?: PHP_INT_MAX];
            });

            // OPTIMIZACIÓN 7: Cargar datos adicionales con conexiones correctas
            $conversaciones = HistorialCriteriosConversacion::all();
            $agenda = \App\Models\agenda\Agenda::where('usuario_id', $usuario_id)->get();

            // Tipo inmueble ya lo tenemos en $tipos_inmueble_map, pero también cargamos todos
            $tipo_inmueble = tipo_inmueble::all(); // Conexión mysql (default)
            $zona = Zona::all(); // Conexión mysql (default)

        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Error al obtener los clientes: ' . $e->getMessage());
        }

        return view('clientes.gestionasesores.asesores', compact(
            'clientes',
            'criterios_venta',
            'tipo_inmueble',
            'conversaciones',
            'agenda',
            'usuario_nombre',
            'criterios_mas_antiguos_por_cliente',
            'zona',
            'clientes_ordenados',
            'faltaDevolucion',
        ));
    }
    public function create() {}

    public function store(Request $request) {}

    public function show($id) {}

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function enviarMensaje(Request $request)
    {
      
        DB::beginTransaction();
        try {
            // Validamos los datos que vienen del formulario
            $request->validate(
                [
                    'id_criterio_venta' => 'required',
                    'mensaje' => 'required',
                    'fecha_hora' => 'required',
                    'last_modified_by' => 'required',
                ],
                [
                    'mensaje.required' => 'El mensaje es requerido.',
                    'fecha_hora.required' => 'La fecha y hora es requerida.',
                ]
            );
            // Creamos el historial de conversación
            $historial_creado = HistorialCriteriosConversacion::create([
                'id_criterio_venta' => $request->id_criterio_venta,
                'mensaje' => $request->mensaje,
                'fecha_hora' => $request->fecha_hora,
                'last_modified_by' => $request->last_modified_by,
            ]);

            return response()->json([
                'success' => true,
                'message' => $historial_creado,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage(),
            ]);
        }
    }

    public function getConversacion($criterioId)
    {
        try {
            //obtenemos la conversacion del historial de conversacion
            $conversacion = HistorialCriteriosConversacion::where('id_criterio_venta', $criterioId)
                ->get()
                ->map(function ($item) {
                    $item->tipo = 'conversacion';
                    return $item;
                });

            //obtenemos la conversacion del criterio de ofrecimiento
            $conversacionOfrecimiento = HistorialCodOfrecimiento::where('id_criterio_venta', $criterioId)
                ->get()
                ->map(function ($item) {
                    $item->tipo = 'ofrecimiento';
                    return $item;
                });

            //obtenemos la conversacion del criterio de muestra
            $conversacionMuestra = HistorialCodMuestra::where('id_criterio_venta', $criterioId)
                ->get()
                ->map(function ($item) {
                    $item->tipo = 'muestra';
                    return $item;
                });
            $conversacionConsulta = HistorialCodigoConsulta::where('id_criterio_venta', $criterioId)
                ->get()
                ->map(function ($item) {
                    $item->tipo = 'consulta';
                    return $item;
                });

            // Combinar ambos arrays
            $mensajesCombinados = $conversacion->concat($conversacionOfrecimiento);
            $mensajesCombinados = $mensajesCombinados->concat($conversacionMuestra);
            $mensajesCombinados = $mensajesCombinados->concat($conversacionConsulta);

            // Ordenarlos por fecha_hora ascendente
            $mensajesOrdenados = $mensajesCombinados->sortBy('fecha_hora')->values(); // importante el ->values() para resetear los índices

            return response()->json($mensajesOrdenados);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la conversacion: ' . $e->getMessage(),
            ]);
        }
    }


    public function buscarPropiedadesSimples(Request $request)
    {
        try {
            //obtenemos las propiedades
            $query = DB::table('propiedades')
                ->leftJoin('calle', 'propiedades.id_calle', '=', 'calle.id')
                ->leftJoin('zona', 'propiedades.id_zona', '=', 'zona.id')
                ->select(
                    'propiedades.id',
                    'propiedades.cod_alquiler',
                    'propiedades.cod_venta',
                    'calle.name as calle',
                    'propiedades.numero_calle',
                    'zona.name as zona'
                );

            // Filtro por código
            if ($request->filled('codigo')) {
                $codigo = $request->get('codigo');
                $query->where(function ($q) use ($codigo) {
                    $q->where('propiedades.cod_alquiler', 'like', $codigo . '%')
                        ->orWhere('propiedades.cod_venta', 'like', $codigo . '%');
                });
            }

            // Filtro por calle
            if ($request->filled('calle')) {
                $calle = $request->get('calle');
                $query->where(function ($q) use ($calle) {
                    $q->where('calle.name', 'like', '%' . $calle . '%')
                        ->orWhere('propiedades.numero_calle', 'like', '%' . $calle . '%');
                });
            }

            $propiedades = $query->limit(20)->get();

            return response()->json($propiedades);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function modificarDatosPersonales(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            //obtenemos el cliente
            $cliente = Clientes::findOrFail($id);

            //actualizamos el cliente
            $cliente->update([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'observaciones' => $request->observaciones,
                'nombre_de_inmobiliaria' => $request->nombre_de_inmobiliaria,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'cliente' => [
                    'id' => $cliente->id_cliente,
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'observaciones' => $cliente->observaciones,
                    'nombre_de_inmobiliaria' => $cliente->nombre_de_inmobiliaria
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al modificar el cliente.',
            ]);
        }
    }


    public function modificarCriterio(Request $request, $id)
    {
        //guardamos el criterio real, que no es el mismo que el que llega por funcion
        $criterio = $request->id_criterio;
        //iniciamos la transaccion
        DB::beginTransaction();
        //iniciamos el try catch
        try {
            //actualizamos el criterio
            CriterioBusquedaVenta::where('id_criterio_venta', $criterio)->update([
                'id_categoria' => $request->categoria,
                'id_tipo_inmueble' => $request->tipo_inmueble,
                'id_zona' => $request->zona,
                'cant_dormitorios' => $request->dormitorios,
                'cochera' => $request->cochera,
                'observaciones_criterio_venta' => $request->observaciones_criterio_venta,
                'estado_criterio_venta' => $request->estado_criterio_venta,
                'precio_hasta' => $request->precio_hasta,
            ]);
            //confirmamos la transaccion
            DB::commit();
            // Get the updated criterio with relationships
            $criterioActualizado = CriterioBusquedaVenta::with('tipoInmueble')->find($criterio);

            // Get the tipo_inmueble relationship data
            $tipoInmueble = $criterioActualizado->tipoInmueble;

            // Format the date
            $fechaFormateada = \Carbon\Carbon::parse($criterioActualizado->fecha_criterio_venta)->format('d/m/Y');

            // Return the updated data
            return response()->json([
                'success' => true,
                'message' => 'Criterio actualizado correctamente',
                'criterio' => [
                    'id_criterio_venta' => $criterioActualizado->id_criterio_venta,
                    'tipo_inmueble' => $tipoInmueble ? $tipoInmueble->inmueble : 'Tipo no especificado',
                    'cant_dormitorios' => $criterioActualizado->cant_dormitorios,
                    'estado_criterio_venta' => $criterioActualizado->estado_criterio_venta,
                    'fecha_formateada' => $fechaFormateada,
                    'precio_hasta' => $criterioActualizado->precio_hasta,
                    'id_categoria' => $criterioActualizado->id_categoria,
                    'tipo_inmueble_id' => $criterioActualizado->id_tipo_inmueble
                ]
            ]);
        } catch (\Exception $e) {
            //si hay error, deshacemos la transaccion
            DB::rollBack();
            //retornamos con mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al modificar el criterio.'
            ], 500);
        }
    }

    public function getPropiedad($propiedadId)
    {
        //obtenemos todos los datos de la propiedad con el servicio pasandole un id
        $propiedad = $this->propiedadService->obtenerPropiedadesPorId($propiedadId);

        //retornamos la propiedad
        return response()->json($propiedad);
    }
    public function guardarHistorialCodOfrecimiento(Request $request)
    {

        //iniciamos la transaccion
        DB::beginTransaction();
        try {

            $propiedad = $request->input('propiedad');
            $idUsuario = $request->input('id_usuario');
            $id_calle = $propiedad['id_calle'];
            $calle = Calle::where('id', $id_calle)->first()->name;


            // Guardar en el historial
            HistorialCodOfrecimiento::create([
                'codigo_ofrecimiento' => $propiedad['cod_venta'],
                'mensaje' => 'Propiedad ofrecida Codigo: ' . $propiedad['cod_venta'] . ' - Direccion: ' . $calle . ' ' . $propiedad['numero_calle'],
                'direccion' => $calle . ' ' . $propiedad['numero_calle'],
                'fecha_hora' => now(),
                'last_modified_by' => session()->get('usuario_id'),
                'id_criterio_venta' => $idUsuario,
            ]);
            //confirmamos la transaccion
            DB::commit();
            //retornamos con mensaje de exito
            return response()->json([
                'success' => true,
                'message' => 'Propiedad asignada correctamente',

            ]);
        } catch (\Exception $e) {
            //si hay error, deshacemos la transaccion
            DB::rollBack();
            //retornamos con mensaje de error
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar la propiedad: '
            ], 500);
        }
    }
    public function devolverMensaje(Request $request, $id)
    {
        try {

            if ($request->tipo == 'ofrecimiento') {
                HistorialCodOfrecimiento::where('id', $request->id_mensaje)->update([
                    'devolucion' => $request->devolucion,
                    'fecha_devolucion' => now(),
                    'last_modified_by' => session()->get('usuario_id'),
                ]);
            } elseif ($request->tipo == 'muestra') {
                HistorialCodMuestra::where('id', $request->id_mensaje)->update([
                    'devolucion' => $request->devolucion,
                    'fecha_devolucion' => now(),
                    'last_modified_by' => session()->get('usuario_id'),
                ]);
            } elseif ($request->tipo == 'consulta') {
                HistorialCodigoConsulta::where('id', $request->id_mensaje)->update([
                    'devolucion' => $request->devolucion,
                    'fecha_devolucion' => now(),
                    'last_modified_by' => session()->get('usuario_id'),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Mensaje devuelto correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al devolver el mensaje: ' . $e->getMessage()]);
        }
    }
    public function obtenerHistorialCod($id)
    {
        try {
            $historialOfrecimiento = HistorialCodOfrecimiento::where('id_criterio_venta', $id)
                ->get();

            $historialMuestra = HistorialCodMuestra::where('id_criterio_venta', $id)
                ->get();

            $historialConsulta = HistorialCodigoConsulta::where('id_criterio_venta', $id)
                ->get();

            $historial = $historialOfrecimiento->concat($historialMuestra)
                ->concat($historialConsulta)
                ->sortBy('fecha_hora');  // Ordena la colección completa por fecha_hora en orden ascendente

            return response()->json($historial->values()->all());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener el historial: ' . $e->getMessage()]);
        }
    }
}
