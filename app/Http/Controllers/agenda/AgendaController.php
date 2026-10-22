<?php

namespace App\Http\Controllers\agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\agenda\Notas;
use App\Models\cliente\clientes;
use App\Models\At_cl\Propiedad;
use App\Models\agenda\Sectores;
use App\Models\agenda\Agenda;
use App\Models\cliente\HistorialCodMuestra;
use App\Models\At_cl\Calle;
use App\Models\cliente\CriterioBusquedaVenta;
use App\Models\cliente\HistorialCriteriosConversacion;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\usuarios_y_permisos\Usuario;


/**
 * Controlador de la agenda comercial.
 *
 * Gestiona la consulta de clientes por teléfono, la creación y modificación
 * de notas, la baja lógica de eventos y la carga de agendas por sector y
 * usuario. Las operaciones de escritura se ejecutan dentro de transacciones
 * para mantener consistentes la nota y los historiales comerciales asociados.
 */
class AgendaController extends Controller
{
    // Atributos protegidos para el ID y los datos del usuario autenticado
    protected $usuario_id;
    protected $usuario;

    public function __construct()
    {
        // Se obtiene el ID del usuario desde la sesión
        $this->usuario_id = session('usuario_id');
    }



    /**
     * Busca clientes por coincidencia parcial de teléfono y devuelve su
     * historial de notas con la propiedad y el asesor asociados.
     *
     * @param string $telefono Fragmento o número completo de teléfono.
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarClientesPorTelefono($telefono)
    {
        // Cargar clientes con sus notas en una sola consulta
        $clientes = clientes::where('telefono', 'like', "%{$telefono}%")
            ->with(['notas' => function ($query) {
                $query->with(['propiedad', 'usuario'])
                    ->orderBy('fecha', 'desc');
            }])
            ->get(['id_cliente', 'nombre', 'telefono']);

        $resultado = $clientes->map(function ($cliente) {
            $historial = $cliente->notas
                ->sortByDesc(fn($n) => $n->fecha . ' ' . $n->hora_inicio)->values()
                ->map(function ($nota) {
                    return [
                        'fecha'        => Carbon::parse($nota->fecha)->format('d-m-Y'),
                        'inmueble'     => $nota->propiedad->calle->name ?? '-',
                        'numero_calle' => $nota->propiedad->numero_calle ?? '-',
                        'hora_inicio'  => Carbon::parse($nota->hora_inicio)->format('H:i'),
                        'asesor'       => $nota->usuario->username ?? 'Sin asesor',
                        'activo'       => $nota->activo == 1 ? 'Activo' : 'Baja',
                        'cod_alquiler' => $nota->propiedad->cod_alquiler ?? '',
                    ];
                })->values();

            return [
                'cliente' => [
                    'id_cliente' => $cliente->id_cliente,
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                ],
                'historial' => $historial
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Registra una nota en la agenda y, según el sector y el criterio
     * recibido, deja el registro correspondiente en los historiales
     * comerciales.
     *
     * El proceso se ejecuta dentro de una transacción. Primero valida que
     * exista la agenda del sector y usuario, luego crea o actualiza el cliente
     * cuando se envía un teléfono, verifica que no haya otra nota activa en el
     * mismo horario y finalmente crea la nota.
     *
     * Si llega `criterioSeleccionado`, exige una propiedad, registra la muestra
     * en `HistorialCodMuestra` y vincula la nota al cliente del criterio. Para
     * el sector 2 (Ventas), también registra la conversación o la muestra
     * cuando la nota se carga con teléfono.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //Log::info('llego la informacion', $request->all());
        //dd($request->all());
        $cliente = null;

        DB::beginTransaction(); // Inicia la transacción

        try {
            // Buscar la agenda correspondiente al sector y usuario
            $agenda = Agenda::where('sector_id', $request->sector)
                ->where('usuario_id', $request->usuario)
                ->first();

            if (!$agenda) {
                // Si no existe la agenda, se responde con error 404.
                return response()->json(['error' => 'No se encontró una agenda válida para este sector y usuario'], 404);
            }
            // Normaliza las horas recibidas para almacenarlas como HH:mm:ss.
            $horaInicioRedondeada = $request->horaInicio;
            $horaFinRedondeada = $request->horaFin;

            if (isset($request->propiedad['id'])) {
                $propiedadV = $request->propiedad['id'];
            } else {
                $propiedadV = null;
            }

            $usuario_id = auth('api')->id();
            if ($request->telefono) {
                //Log::info('entro al telefono');
                $cliente = Clientes::where('telefono', $request->telefono)->first();
                if (!$cliente) {
                    $cliente = Clientes::create([
                        'nombre' => $request->nombreCliente,
                        'telefono' => $request->telefono,
                        'usuario_id' => $usuario_id,
                        'ingreso' => 'Agenda',
                        'id_asesor_alquiler' => $request->usuario
                    ]);
                }
                if ($cliente) {
                    $cliente->update([
                        'nombre' => $request->nombreCliente,
                        'telefono' => $request->telefono,
                        'usuario_id' => $usuario_id,
                        'ingreso' => 'Agenda',
                        //'id_asesor_alquiler' => $request->usuario
                    ]);
                }
                //Log::info('cliente', ['cliente' => $cliente]);
            }

            // Crea la nota con las horas normalizadas.
            try {
                $nuevaHoraInicio = $horaInicioRedondeada . ':00';
                // La nota existente ocupa el intervalo [hora_inicio, hora_fin).
                // Hay conflicto si la nueva hora de inicio cae dentro de ese intervalo.
                $repetida = Notas::where('fecha', $request->fecha)
                    ->where('hora_inicio', '<=', $nuevaHoraInicio)
                    ->where('hora_fin',    '>',  $nuevaHoraInicio)
                    ->where('usuario_id', $request->usuario)
                    ->where('agenda_id', $agenda->id)
                    ->where('activo', 1)
                    ->first();
                if ($repetida) {
                    //Log::info('Ya existe una nota en ese horario');
                    return response()->json([
                        'status' => 'error',
                        'message' => "Ya existe una nota en ese horario (de {$repetida->hora_inicio} a {$repetida->hora_fin})"
                    ], 404);
                }
                $notas = Notas::create([
                    'hora_inicio' => $horaInicioRedondeada . ':00', //
                    'hora_fin'    => $horaFinRedondeada . ':00', //
                    'descripcion' => $request->descripcion, //
                    'propiedad_id' => $propiedadV,
                    'cliente_id' => $cliente ? $cliente->id_cliente : null,
                    'creado_por' => $usuario_id, //
                    'usuario_id' => $request->usuario, //
                    'agenda_id' => $agenda->id,
                    'fecha' => $request->fecha, //
                    'realizado' => 0,
                    'devoluciones' => $request->devolucion ? $request->devolucion : null,
                ]);
            } catch (\Exception $e) {
                Log::error('Error al crear nota', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
            }

            //Log::info('Nota creada correctamente');
            // Si se proporciona un criterio, crear el evento en el historial
            if ($request->criterioSeleccionado != null) {
                if ($propiedadV === null) {
                    //Log::error('Propiedad no encontrada');
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Ingrese una propiedad'
                    ], 404);
                }
                //Log::info('antes de buscar cliente');

                $busca_cliente = CriterioBusquedaVenta::where('id_criterio_venta', $request->criterioSeleccionado)->first();


                //Log::info('despues de buscar cliente');
                // Busca la propiedad por su ID para registrar la muestra.
                $propiedadNC = Propiedad::where('id', $propiedadV)->first();
                //Log::info('despues de buscar propiedad');
                // Buscar el nombre de la calle
                $calleName = Calle::where('id', $propiedadNC->id_calle)->first()->name;

                // Dia formato dd/mm/yyyy
                $fecha = Carbon::parse($request->fecha)->format('d/m/Y');

                //Log::info('antes de crear historial');
                // Crear el evento en el historial
                HistorialCodMuestra::create([
                    'codigo_muestra' => $propiedadNC->cod_venta,
                    'mensaje' => 'Se muestra la propiedad: ' . $propiedadNC->cod_venta . ' - Direccion: ' .
                        $calleName . ' ' . $propiedadNC->numero_calle . ' a las ' . $horaInicioRedondeada .
                        ' en el dia ' . $fecha,
                    'direccion' => $calleName . ' ' . $propiedadNC->numero_calle,
                    'fecha_hora' => now(),
                    'last_modified_by' => $usuario_id,
                    'id_criterio_venta' => $request->criterioSeleccionado
                ]);

                //Log::info('despues de crear historial');
                // El criterio ya tiene un cliente asociado; se vincula la nota a ese cliente.
                $notas->update([
                    'cliente_id' => $busca_cliente->id_cliente
                ]);

                // Respuesta exitosa después de registrar la muestra.
                return response()->json([
                    'success' => true,
                    //'evento' => $evento
                ]);
            } elseif ($request->sector == 2 && $request->telefono && $request->propiedad == null) {


                //Log::info($request->all());
                $cliente = Clientes::where('telefono', $request->telefono)->first();
                if (!$cliente) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontró un cliente con ese teléfono'
                    ], 404);
                }
                // Se toma el criterio de venta más reciente del cliente.
                $criterio_venta = CriterioBusquedaVenta::where('id_cliente', $cliente->id_cliente)
                    ->orderBy('id_criterio_venta', 'desc')
                    ->first();

                // Se registra la conversación sobre el criterio de venta más reciente.
                HistorialCriteriosConversacion::create([
                    'id_criterio_venta' => $criterio_venta->id_criterio_venta,
                    'mensaje' => $request->descripcion ?? 'conversacion generada por agenda',
                    'last_modified_by' => $usuario_id,
                    'fecha_hora' => now()
                ]);
            } elseif ($request->sector == 2 && $request->telefono && $request->propiedad != null) {
                //Log::info($request->all());
                $cliente_buscado = Clientes::where('telefono', $request->telefono)->first();
                $criterio_buscado = CriterioBusquedaVenta::where('id_cliente', $cliente_buscado->id_cliente)
                    ->orderBy('id_criterio_venta', 'desc')
                    ->first();
                HistorialCodMuestra::create([
                    'codigo_muestra' => $request->propiedad['cod_venta'],
                    'mensaje' => 'Se muestra la propiedad: ' . $request->propiedad['cod_venta'] . ' - Direccion: ' .
                        $request->propiedad['calle'] . ' ' . $request->propiedad['numero_calle'] . ' a las ' . $horaInicioRedondeada .
                        ' en el dia ' . Carbon::parse($request->fecha)->format('d/m/Y'),
                    'fecha_hora' => now(),
                    'last_modified_by' => $request->usuario,
                    'id_criterio_venta' => $criterio_buscado->id_criterio_venta,
                    'devolucion' => null,
                    'fecha_devolucion' => null,
                    'direccion' => $request->propiedad['calle'] . ' ' . $request->propiedad['numero_calle'],
                ]);
            }

            DB::commit(); // Confirmar transacción

            return response()->json([
                'success' => true,
                'message' => 'Evento agregado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios ante error

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al agregar el evento.' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza una nota existente y, si se envía un teléfono, crea o
     * actualiza el cliente asociado.
     *
     * Conserva la propiedad actual cuando la solicitud no incluye una nueva y
     * valida el solapamiento horario excluyendo la propia nota para permitir
     * guardar sin cambiar el horario.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modificar(Request $request)
    {
        //Log::info('esto es lo que me llega', ['request' => $request->all()]);
        $cliente = null;

        DB::beginTransaction();

        try {
            // Buscar la nota existente
            $nota = Notas::find($request->id);
            if (!$nota) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se encontró la nota a modificar'
                ], 404);
            }

            // Buscar la agenda correspondiente al sector y usuario
            $agenda = Agenda::where('sector_id', $request->sector)
                ->where('usuario_id', $request->usuario)
                ->first();

            if (!$agenda) {
                return response()->json(['error' => 'No se encontró una agenda válida para este sector y usuario'], 404);
            }

            $horaInicioRedondeada = substr($request->horaInicio, 0, 5);
            $horaFinRedondeada = substr($request->horaFin, 0, 5);

            if (isset($request->propiedad['id'])) {
                $propiedadV = $request->propiedad['id'];
            } else {
                $propiedadV = $nota->propiedad_id; // Mantener la propiedad existente si no se envía una nueva
            }

            $usuario_id = auth('api')->id();

            // Manejo de cliente (igual que store)
            if ($request->telefono) {
                $cliente = Clientes::where('telefono', $request->telefono)->first();
                if (!$cliente) {
                    $cliente = Clientes::create([
                        'nombre' => $request->nombreCliente,
                        'telefono' => $request->telefono,
                        'usuario_id' => $usuario_id,
                        'ingreso' => 'Agenda',
                        'id_asesor_alquiler' => $request->usuario
                    ]);
                }
                if ($cliente) {
                    $cliente->update([
                        'nombre' => $request->nombreCliente,
                        'telefono' => $request->telefono,
                        'usuario_id' => $usuario_id,
                        'ingreso' => 'Agenda',
                    ]);
                }
            }

            // Valida que no haya otra nota activa en el mismo intervalo. Se
            // excluye la nota actual para permitir guardar sin cambiar la hora.
            $nuevaHoraInicio = $horaInicioRedondeada . ':00';
            $repetida = Notas::where('fecha', $request->fecha)
                ->where('hora_inicio', '<=', $nuevaHoraInicio)
                ->where('hora_fin', '>', $nuevaHoraInicio)
                ->where('usuario_id', $request->usuario)
                ->where('agenda_id', $agenda->id)
                ->where('activo', 1)
                ->where('id', '!=', $nota->id) // Excluir la nota que se está modificando
                ->first();

            if ($repetida) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Ya existe una nota en ese horario (de {$repetida->hora_inicio} a {$repetida->hora_fin})"
                ], 404);
            }

            // Actualizar la nota
            $nota->update([
                'hora_inicio' => $horaInicioRedondeada . ':00',
                'hora_fin'    => $horaFinRedondeada . ':00',
                'descripcion' => $request->descripcion,
                'propiedad_id' => $propiedadV,
                'cliente_id' => $cliente ? $cliente->id_cliente : null,
                'usuario_id' => $request->usuario,
                'agenda_id' => $agenda->id,
                'fecha' => $request->fecha,
                'devoluciones' => $request->devolucion ? $request->devolucion : null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nota modificada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al modificar la nota. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactiva lógicamente una nota sin eliminarla de la base de datos.
     *
     * Conserva el motivo de la baja y el usuario que la realizó, y marca
     * `activo = 0` para que deje de mostrarse en la agenda.
     *
     * @param int|string $id
     * @param string $motivo
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, $motivo)
    {
        // Log::info('entro', ['id' => $id, 'motivo' => $motivo]);
        // Inicia la transacción
        DB::beginTransaction();
        try {
            // Buscar la nota por ID
            $notas = Notas::find($id);
            $usuario_id = auth('api')->id();


            if ($notas) {
                // Cambiar el estado de la nota a inactiva (activo = 0)
                $notas->update(['activo' => 0, 'motivo' => $motivo, 'quien_borro' => $usuario_id]);

                //$fecha = request('fecha', date('Y-m-d'));

                DB::commit(); // Confirmar transacción
                return response()->json([
                    'success' => true,
                    'message' => 'Evento desactivado correctamente'
                ]);
            }
            DB::rollBack(); // La nota no existe; se revierte la transacción.
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el evento'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios ante error
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al desactivar el evento.' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Devuelve los sectores disponibles para asignar una agenda.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarSectores()
    {
        return Sectores::select('id', 'nombre')->get();
    }


    /**
     * Obtiene las agendas de un sector con las notas activas de una fecha y
     * los datos del cliente y la propiedad necesarios para el tablero.
     *
     * Además de la nota consultada, incluye todas las notas del cliente para
     * que el frontend pueda mostrar su historial completo.
     *
     * @param int|string $id_sector
     * @param string $fecha Fecha en formato Y-m-d.
     * @return \Illuminate\Http\JsonResponse
     */
    public function traerUsuarioSector($id_sector, $fecha)
    {
        $agendas = Agenda::with([
            'usuario:id,username',

            // Notas del usuario: solo las activas de la fecha consultada.
            'notas' => function ($q) use ($fecha) {
                $q->where('fecha', $fecha)
                    ->where('activo', 1)
                    ->with([
                        // Se incluye el cliente con todas sus notas para mostrar su historial.
                        'cliente.notas',
                        'propiedad.calle',
                        'propiedad.estadoVenta',
                        'propiedad.estadoAlquiler'
                    ]);
            }
        ])
            ->where('sector_id', $id_sector)
            ->get();

        $resultado = $agendas->map(function ($agenda) {

            return [
                'usuario_id' => $agenda->usuario_id,
                'nombre' => $agenda->usuario->username ?? '',
                'nota' => $agenda->notas->map(function ($item) {

                    return [
                        'id' => $item->id,
                        'agenda_id' => $item->agenda_id,
                        'descripcion' => $item->descripcion,
                        'usuario_id' => $item->usuario_id,
                        'hora_inicio' => $item->hora_inicio,
                        'hora_fin' => $item->hora_fin,
                        'activo' => $item->activo,
                        // Se resuelve el nombre del usuario que creó la nota.
                        'creado_por' => \App\Models\usuarios_y_permisos\Usuario::find($item->creado_por)->username ?? '',

                        'id_cliente' => $item->cliente->id_cliente ?? '',
                        'nombreCliente' => $item->cliente->nombre ?? '',
                        'telfCliente' => $item->cliente->telefono ?? '',

                        'propiedad_cod_venta' => $item->propiedad->cod_venta ?? '',
                        'propiedad_cod_alquiler' => $item->propiedad->cod_alquiler ?? '',
                        'propiedad_calle' => $item->propiedad->calle->name ?? '',
                        'propiedad_numero_calle' => $item->propiedad->numero_calle ?? '',
                        'propiedad_estado_venta' => $item->propiedad->estadoVenta->name ?? '',
                        'propiedad_estado_alquiler' => $item->propiedad->estadoAlquiler->name ?? '',

                        'fecha' => $item->fecha,
                        'realizado' => $item->realizado,

                        // Todas las notas del cliente (activas e inactivas), de la más reciente a la más antigua.
                        'notas_cliente' => $item->cliente
                            ? $item->cliente->notas->sortByDesc(fn($n) => $n->fecha . ' ' . $n->hora_inicio)->values()->map(function ($n) {
                                return [
                                    'id' => $n->id,
                                    'fecha' => $n->fecha, //
                                    'hora_inicio' => $n->hora_inicio, //
                                    'activo' => $n->activo, //
                                    'cod_alquiler' => $n->propiedad->cod_alquiler ?? '',
                                    'asesor' => $n->usuario->username ?? '',
                                    'inmueble' => $n->propiedad->calle->name ?? '',
                                    'numero_calle' => $n->propiedad->numero_calle ?? '',
                                ];
                            })
                            : []
                    ];
                })
            ];
        });
        //Log::info('resultado', ['resultado' => $resultado]);
        return response()->json($resultado);
    }

    /**
     * Devuelve las notas activas de las agendas del usuario autenticado.
     *
     * `diarias` contiene las notas de la fecha actual y `semanales` las notas
     * posteriores; ambas incluyen la propiedad y su calle.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function traerAgendaDiaria()
    {
        $data = [];
        $usuario_id = auth('api')->id();
        $fecha = now()->toDateString();
        //Log::info('fecha', ['fecha' => $fecha]);

        // Se obtienen las agendas del usuario para consultar sus notas por agenda_id.
        $sectores = Agenda::where('usuario_id', $usuario_id)
            ->whereNotNull('sector_id')
            ->pluck('id');
        //Log::info('sectores', ['sectores' => $sectores]);

        // Se consultan todas las notas de cualquiera de las agendas del usuario.
        $notas = Notas::whereIn('agenda_id', $sectores)
            ->where('activo', 1)
            ->where('fecha', $fecha)
            // Para traer la relación 'calle' anidada dentro de 'propiedad', debes incluir el foreign key 'id_calle'
            ->with(['propiedad:id,cod_alquiler,cod_venta,id_calle,numero_calle', 'propiedad.calle',])
            ->get();

        $data = $notas;

        // Notas futuras de las mismas agendas.
        $notasSemanales = Notas::whereIn('agenda_id', $sectores)
            ->where('activo', 1)
            ->where('fecha', '>', $fecha)
            // Para traer la relación 'calle' anidada dentro de 'propiedad', debes incluir el foreign key 'id_calle'
            ->with(['propiedad:id,cod_alquiler,cod_venta,id_calle,numero_calle', 'propiedad.calle',])
            ->get();



        return response()->json(['diarias' => $data, 'semanales' => $notasSemanales]);
    }
}
