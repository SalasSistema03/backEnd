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
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\json;

class AsesoresController
{
    public $propiedadService;
    protected $accessService;
    protected $usuario_id;
    protected $usuario;


    public function __construct(PropiedadService $propiedadService)
    {
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        //$this->usuario = Usuario::find($this->usuario_id);
        $this->propiedadService = $propiedadService;
        $this->accessService = new PermitirAccesoPropiedadService($this->usuario_id);
    }


    public function Asesores()
    {
        $id_usuario = auth('api')->id();

        try {
            $clientesOrdenados = DB::connection('mysql5')
                ->table('clientes as c')
                ->leftJoin('criterio_busqueda_venta as cbv', 'c.id_cliente', '=', 'cbv.id_cliente')
                ->where('c.id_asesor_venta', $id_usuario)
                ->selectRaw('c.id_cliente')
                ->groupBy('c.id_cliente')


                ->orderByRaw('
                                CASE
                                    WHEN MAX(CASE WHEN cbv.estado_criterio_venta = "Activo" THEN 1 ELSE 0 END) = 1 THEN 1
                                    WHEN MAX(CASE WHEN cbv.estado_criterio_venta = "Finalizado" THEN 1 ELSE 0 END) = 1 THEN 2
                                    ELSE 3
                                END
                            ')
                ->orderByRaw('
                                MIN(
                                    CASE
                                        WHEN cbv.estado_criterio_venta = "Activo" THEN
                                            CASE
                                                WHEN cbv.id_categoria IS NULL THEN 1
                                                WHEN cbv.id_categoria = "Potable" THEN 2
                                                WHEN cbv.id_categoria = "Medio" THEN 3
                                                WHEN cbv.id_categoria = "No Potable" THEN 4
                                                ELSE 5
                                            END
                                    END
                                )
                            ')
                ->orderByRaw('
                                MAX(
                                    CASE
                                        WHEN cbv.estado_criterio_venta = "Activo" THEN cbv.fecha_criterio_venta
                                        WHEN cbv.estado_criterio_venta = "Finalizado" THEN cbv.fecha_criterio_venta
                                        ELSE cbv.fecha_criterio_venta
                                    END
                                ) DESC
                            ')
                ->pluck('id_cliente');



            $clientes = \App\Models\cliente\clientes::with([
                'criteriosOrdenados.zona',
                'criteriosOrdenados.tipoInmueble',
                'criteriosOrdenados.historialMuestras',
                'criteriosOrdenados.historialOfrecimientos',
                'criteriosOrdenados.historialConsultas',
                'criteriosOrdenados.historialConversaciones',
            ])
                ->whereIn('id_cliente', $clientesOrdenados)
                ->get()
                ->sortBy(function ($cliente) use ($clientesOrdenados) {
                    return $clientesOrdenados->search($cliente->id_cliente);
                })
                ->values();



            if ($clientes->isEmpty()) {
                return response()->json([
                    'error' => 'No se encontraron clientes'
                ], 404);
            }

            return response()->json([
                'clientes' => $clientes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function create() {}

    public function store(Request $request) {}

    public function show($id) {}

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function enviarMensaje(Request $request)
    {
        /*  Log::info('enviarMensaje', ['request' => $request->all()]);
        dd('enviarMensaje'); */

        DB::beginTransaction();
        try {
            $idUsuario = auth('api')->id();
            // Validamos los datos que vienen del formulario
            /*  $request->validate(
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
            ); */
            // Creamos el historial de conversación
            $historial_creado = HistorialCriteriosConversacion::create([
                'id_criterio_venta' => $request->id_criterio_venta,
                'mensaje' => $request->mensaje,
                'fecha_hora' => now(),
                'last_modified_by' => $idUsuario,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Propiedad asignada correctamente',
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

    public function modificarDatosPersonales(Request $request)
    {
        /*  Log::info('modificarDatosPersonales', ['request' => $request->all()]);
        dd('hola'); */
        DB::beginTransaction();

        try {
            //obtenemos el cliente
            $cliente = Clientes::findOrFail($request->id_cliente);

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
                /*  'cliente' => [
                    'id' => $cliente->id_cliente,
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'observaciones' => $cliente->observaciones,
                    'nombre_de_inmobiliaria' => $cliente->nombre_de_inmobiliaria
                ] */
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al modificar el cliente.',
            ]);
        }
    }


    public function modificarCriterio(Request $request)
    {

        Log::info('modificarCriterio', ['request' => $request->all()]);

        /*dd('hola'); */
        //guardamos el criterio real, que no es el mismo que el que llega por funcion
        $criterio = $request->id_criterio_venta;
        //iniciamos la transaccion
        DB::beginTransaction();
        //iniciamos el try catch
        try {
            //actualizamos el criterio
            CriterioBusquedaVenta::where('id_criterio_venta', $criterio)->update([
                'id_categoria' => $request->id_categoria,
                'id_tipo_inmueble' => $request->id_tipo_inmueble,
                'id_zona' => $request->id_zona,
                'cant_dormitorios' => $request->cant_dormitorios,
                'cochera' => $request->cochera,
                'observaciones_criterio_venta' => $request->observaciones,
                'estado_criterio_venta' => $request->estado_criterio_venta,
                'precio_hasta' => $request->precio_hasta,
            ]);
            //confirmamos la transaccion
            DB::commit();
            /*  // Get the updated criterio with relationships
            $criterioActualizado = CriterioBusquedaVenta::with('tipoInmueble')->find($criterio);

            // Get the tipo_inmueble relationship data
            $tipoInmueble = $criterioActualizado->tipoInmueble;

            // Format the date
            $fechaFormateada = \Carbon\Carbon::parse($criterioActualizado->fecha_criterio_venta)->format('d/m/Y'); */

            // Return the updated data
            return response()->json([
                'success' => true,
                'message' => 'Criterio actualizado correctamente',
                /* 'criterio' => [
                    'id_criterio_venta' => $criterioActualizado->id_criterio_venta,
                    'tipo_inmueble' => $tipoInmueble ? $tipoInmueble->inmueble : 'Tipo no especificado',
                    'cant_dormitorios' => $criterioActualizado->cant_dormitorios,
                    'estado_criterio_venta' => $criterioActualizado->estado_criterio_venta,
                    'fecha_formateada' => $fechaFormateada,
                    'precio_hasta' => $criterioActualizado->precio_hasta,
                    'id_categoria' => $criterioActualizado->id_categoria,
                    'tipo_inmueble_id' => $criterioActualizado->id_tipo_inmueble
                ] */
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
            $idUsuario = auth('api')->id();


            // Guardar en el historial
            HistorialCodOfrecimiento::create([
                'codigo_ofrecimiento' => $request->cod_venta,
                'mensaje' => 'Propiedad ofrecida Codigo: ' . $request->cod_venta . ' - Direccion: ' . $request->calle,
                'direccion' => $request->calle,
                'fecha_hora' => now(),
                'last_modified_by' => $idUsuario,
                'id_criterio_venta' => $request->id_criterio_venta,
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
    public function devolverMensaje(Request $request)
    {

        //Log::info('devolverMensaje', ['request' => $request->all()]);
        $idUsuario = auth('api')->id();
        //dd('devolverMensaje');
        try {

            if ($request->tipo == 'ofrecimiento') {
                HistorialCodOfrecimiento::where('id', $request->item)->update([
                    'devolucion' => $request->mensaje,
                    'fecha_devolucion' => now(),
                    'last_modified_by' =>  $idUsuario,
                ]);
            } elseif ($request->tipo == 'muestra') {
                HistorialCodMuestra::where('id', $request->item)->update([
                    'devolucion' => $request->mensaje,
                    'fecha_devolucion' => now(),
                    'last_modified_by' =>  $idUsuario,
                ]);
            } elseif ($request->tipo == 'consulta') {
                HistorialCodigoConsulta::where('id', $request->item)->update([
                    'devolucion' => $request->mensaje,
                    'fecha_devolucion' => now(),
                    'last_modified_by' =>  $idUsuario,
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
