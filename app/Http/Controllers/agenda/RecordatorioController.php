<?php

namespace App\Http\Controllers\agenda;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\agenda\Agenda;
use Carbon\Carbon;
use App\Models\agenda\Recordatorio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\RecordatorioNotificacion;
use App\Models\At_cl\Usuario;
use App\Models\cliente\clientes;
use App\Services\RecordatorioService;

class RecordatorioController
{
    protected $recordatorioService;

    public function __construct(RecordatorioService $recordatorioService)
    {
        $this->recordatorioService = $recordatorioService;
    }

    public function index()
    {

        $usuario_id = session('usuario_id');
        $agenda = Agenda::where('usuario_id', $usuario_id)->get();
        $hoy = now()->toDateString(); // Obtiene la fecha actual en formato Y-m-d

        // Obtenemos los IDs de los sectores del usuario
        $sectores_ids = $agenda->pluck('sector_id')->filter()->unique()->toArray();

        $recordatorio = Recordatorio::where('activo', 1)
            ->where(function ($query) use ($usuario_id, $sectores_ids, $hoy) {
                // Mis recordatorios
                $query->where('usuario_carga', $usuario_id);

                // O recordatorios de mis sectores
                if (!empty($sectores_ids)) {
                    $query->orWhereHas('agenda', function ($q) use ($sectores_ids) {
                        $q->whereIn('sector_id', $sectores_ids);
                    });
                }
            })
            ->where(function ($query) use ($hoy) {
                $query->whereDate('fecha_inicio', '>', $hoy); // Fecha inicio es hoy
            })
            ->with('agenda.sector')
            ->orderBy('fecha_actualizacion', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        $recordatorioHoy = Recordatorio::where('activo', 1)
            ->where(function ($query) use ($usuario_id, $sectores_ids, $hoy) {
                // Mis recordatorios
                $query->where('usuario_carga', $usuario_id);

                // O recordatorios de mis sectores
                if (!empty($sectores_ids)) {
                    $query->orWhereHas('agenda', function ($q) use ($sectores_ids) {
                        $q->whereIn('sector_id', $sectores_ids);
                    });
                }
            })
            ->where(function ($query) use ($hoy) {
                $query->whereDate('fecha_inicio', '<=', $hoy); // Fecha inicio es hoy
            })
            ->with('agenda.sector')
            ->orderBy('hora', 'asc')
            ->get();

        return view('agenda.recordatorio.recordatorio', compact('agenda', 'recordatorio', 'recordatorioHoy'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //Log::info('Logs provenientes de clientescontroller ', $request->all());
        DB::beginTransaction();

        try {
            // Inicializar por seguridad
            $fecha_actualizacion = null;
            $fecha_fin = null;


            if ($request->repetir == 1) {
                if ($request->intervalo == 'Diario') {
                    $fecha_actualizacion = Carbon::parse($request->fecha_inicio)->addDays((int)$request->cantidad);
                    $fecha_fin = $fecha_actualizacion;
                } else if ($request->intervalo == 'Mensual') {
                    $fecha_actualizacion = Carbon::parse($request->fecha_inicio)->addMonths((int)$request->cantidad);
                    $fecha_fin = $fecha_actualizacion;
                }
            } else {
                if ($request->intervalo == 'Diario') {
                    $fecha_actualizacion = Carbon::parse($request->fecha_inicio)->addDays((int)$request->cantidad);
                    $fecha_fin = Carbon::parse($request->fecha_inicio)->addDays((int)$request->cantidad * (int)$request->repetir);
                } else if ($request->intervalo == 'Mensual') {
                    $fecha_actualizacion = Carbon::parse($request->fecha_inicio)->addMonths(1);
                    $fecha_fin = Carbon::parse($request->fecha_inicio)->addMonths((int)$request->cantidad * (int)$request->repetir);
                }
            }

            $recordatorio = Recordatorio::create([
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $request->fecha_inicio,
                'agenda_id' => $request->agenda_id,
                'hora' => $request->hora,
                'intervalo' => $request->intervalo,
                'cantidad' => $request->cantidad,
                'usuario_carga' => $request->usuario_id,
                'usuario_finaliza' => $request->usuario_finaliza,
                'activo' => 1,
                'fecha_actualizacion' => $fecha_actualizacion,
                'fecha_fin' => $fecha_fin,
                'repetir' => $request->repetir,
            ]);

            $mensaje = [
                'pertenece' => 'recordatorio',
                'id' => $recordatorio->id,
                'descripcion' => $recordatorio->descripcion,
                'fecha' => $recordatorio->fecha_inicio,
                'hora' => $recordatorio->hora,
                'activo' => 1,
                'es_asesor_activo' => 0,
                'es_criterio' => 0,
            ];

            if ($request->agenda_id != null) {
                $agendaRelacionada = Agenda::find($request->agenda_id);
                if ($agendaRelacionada && $agendaRelacionada->sector_id) {
                    // Buscar todos los usuarios del mismo sector
                    $usuariosDelSector = Agenda::where('sector_id', $agendaRelacionada->sector_id)
                        ->pluck('usuario_id')
                        ->unique();

                    foreach ($usuariosDelSector as $usuario_id_sector) {
                        $usuario_sector = Usuario::find($usuario_id_sector);
                        if ($usuario_sector) {
                            $usuario_sector->notify(new RecordatorioNotificacion($mensaje));
                        }
                    }
                }
            } else {
                $usuario = Usuario::find($request->usuario_id);
                if ($usuario) {
                    $usuario->notify(new RecordatorioNotificacion($mensaje));
                }
            }



            DB::commit();

            // Verificar si es una petición AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recordatorio creado exitosamente',
                    'recordatorio' => $recordatorio
                ]);
            }

            return redirect()->route('recordatorio.index')->with('success', 'Recordatorio creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear recordatorio: ' . $e->getMessage());

            // Verificar si es una petición AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el recordatorio: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('recordatorio.index')->with('error', 'Error al crear el recordatorio: ' . $e->getMessage());
        }
    }


    /**
     * Crea un recordatorio asociado a un cliente recién ingresado
     *
     * Este método toma el identificador del asesor proveniente del formulario de cliente,
     * invoca el servicio de recordatorios para generar un nuevo registro y redirige a la
     * vista correspondiente mostrando el resultado de la operación. En caso de no recibir
     * un asesor válido, retorna a la página anterior con un mensaje de error.
     *
     * @param Request $request datos enviados desde el formulario de cliente
     *
     * @return \Illuminate\Http\RedirectResponse redirección hacia la vista de recordatorios o regreso con error
     * @throws \Exception si ocurre un error durante la creación del recordatorio en el servicio
     * @access public
     * @see RecordatorioService::crearDesdeCliente()
     */
    public function storeDesdeClientes(Request $request)
    {
        //Log::info('Logs provenientes de recordatoriocontroller ', $request->all());
        $asesorId = $request->input('cliente.id_asesor');
        $nombreCliente = $request->input('cliente.nombre');

        if ($asesorId) {

            $recordatorio = $this->recordatorioService->crearDesdeCliente($asesorId, $nombreCliente);

            return redirect()
                ->route('recordatorio.index')
                ->with('success', 'Recordatorio creado exitosamente');
        }

        return back()->with('error', 'No se encontró asesor.');
    }

    /**
     * Genera un recordatorio cuando se registran nuevos criterios o propiedades
     * provenientes del módulo de clientes.
     *
     * Este método identifica al asesor responsable a partir de los datos enviados
     * en la solicitud, obtiene el nombre del cliente asociado y delega en el
     * servicio de recordatorios la creación del registro correspondiente.
     *
     * @param  \Illuminate\Http\Request $request solicitud HTTP con criterios o propiedades asignadas
     *
     * @return mixed instancia de Recordatorio o redirección con mensaje de error
     * @throws \Exception si ocurre un error al resolver datos del asesor o del cliente
     * @access public
     */
    public function storeDesdeClientesCriterio(Request $request)
    {
        /* Registra en log los datos recibidos para diagnóstico */
        /*  Log::info('storeDesdeClientesCriterio', $request->all()); */

        /* Busca el asesor desde propiedades_venta o criterios_venta */
        $asesorId =
            $request->input('propiedades_venta.0.usuario_id')
            ?? $request->input('criterios_venta.0.usuario_id')
            ?? null;

        /* Recupera ID del cliente vinculado al criterio */
        $idCliente = $request->input('id_cliente');

        /* Obtiene nombre del cliente; si no existe, asigna un valor por defecto */
        $nombreCliente = clientes::find($idCliente)->nombre ?? 'Cliente desconocido';

        /* Si se identifica correctamente el asesor, se genera el recordatorio */
        if ($asesorId) {

            /* Log::info('ingreso al if');
            Log::info('Datos del asesor', [
                'asesor_id' => $asesorId,
                'nombre'    => $nombreCliente
            ]); */

            /* Crea un recordatorio basado en la carga de criterio */
            $recordatorio = $this->recordatorioService->crearDesdeCriterio(
                $asesorId,
                $nombreCliente
            );

            return $recordatorio;
        }

        /* Si no se puede determinar quién es el asesor, se retorna con error */
        return back()->with('error', 'No se encontró asesor.');
    }


    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}




    /**
     * Actualiza un recordatorio existente
     *
     * Ejecuta una operación transaccional para actualizar el recordatorio indicado.
     * Dependiendo del contenido del request, el método puede finalizar el recordatorio
     * o actualizar sus datos. Posteriormente, se actualizan las notificaciones asociadas.
     * Todas las operaciones se realizan dentro de una transacción para garantizar
     * la integridad de los datos.
     *
     * @param  \Illuminate\Http\Request $request datos enviados desde el formulario de actualización
     * @param  string                   $id      identificador del recordatorio a actualizar
     *
     * @return \Illuminate\Http\RedirectResponse respuesta de redirección hacia la vista índice con un mensaje de estado
     * @throws \Exception                        si ocurre algún error inesperado durante el proceso
     * @access public
     */
    public function update(Request $request, string $id)
    {
        /*  dd($request->all()); */
        /* Inicia la transacción para asegurar la integridad de los cambios */
        DB::beginTransaction();

        try {
            /* Busca el recordatorio; si no existe, lanza un error */
            $recordatorio = Recordatorio::findOrFail($request->id);
            //dd($recordatorio); 

            /* Instancia del servicio responsable de gestionar recordatorios */
            $service = new \App\Services\RecordatorioService;
            //Log::info('antes de entrar al if');
            /* Verifica si el recordatorio debe marcarse como finalizado */
            if ($request->finalizado == 1) {
                /* Finaliza el recordatorio */
                $recordatorio = $service->finalizarRecordatorio($recordatorio);
            } else {
                /* Actualiza los datos del recordatorio con la información enviada */
                $recordatorio = $service->actualizarRecordatorio($recordatorio, $request->all());
            }
            //Log::info('salio del if');
            /* Actualiza las notificaciones asociadas al recordatorio */
            $service->actualizarNotificaciones($recordatorio);

            /* Confirma los cambios realizados durante la transacción */
            DB::commit();

            /* Redirige con mensaje de éxito */
            return redirect()->route('recordatorio.index')
                ->with('success', 'Recordatorio actualizado exitosamente');
        } catch (\Exception $e) {
            /* Revierte la transacción debido a un error */
            DB::rollBack();

            /* Registra el error para depuración */
            //Log::error('Error al actualizar recordatorio: ' . $e->getMessage());

            /* Redirige con mensaje de error */
            return redirect()->route('recordatorio.index')
                ->with('error', 'Error al actualizar el recordatorio: ' . $e->getMessage());
        }
    }


    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $recordatorio = Recordatorio::findOrFail($id); // Mejor usar findOrFail

            $recordatorio->activo = 0;
            $recordatorio->save();

            $notificaciones = DB::connection('mysql4') // o el nombre de la conexión para DB_DATABASE4
                ->table('notifications')
                ->where('data->id', $recordatorio->id)
                ->get();


            foreach ($notificaciones as $notificacion) {
                $data = json_decode($notificacion->data, true);
                $data['activo'] = $recordatorio->activo;


                DB::connection('mysql4')
                    ->table('notifications')
                    ->where('id', $notificacion->id)

                    ->update([
                        'data' => json_encode($data),
                    ]);
            }


            DB::commit();

            return redirect()
                ->route('recordatorio.index')
                ->with('success', 'Recordatorio eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log para depurar si es necesario
            Log::error('Error al eliminar recordatorio: ' . $e->getMessage());

            return redirect()
                ->route('recordatorio.index')
                ->with('error', 'Ocurrió un error al eliminar el recordatorio.');
        }
    }

    /**
     * Marca una notificación como leída para el usuario autenticado
     *
     * Obtiene el ID de la notificación desde la solicitud, valida la existencia del
     * usuario y de la notificación asociada, y actualiza el campo `es_asesor_activo`
     * dentro del payload `data` de la notificación. Devuelve una respuesta JSON
     * indicando el resultado de la operación.
     *
     * @param  \Illuminate\Http\Request $request solicitud HTTP que contiene el ID de la notificación
     *
     * @return \Illuminate\Http\JsonResponse respuesta JSON con el estado de la operación
     * @throws \Exception si ocurre algún error inesperado durante la actualización
     * @access public
     */
    public function marcarNotificacionLeida(Request $request)
    {
        try {
            /* Obtiene el ID de la notificación enviada desde el cliente */
            $notificacionId = $request->input('notificacion_id');
            /*  Log::info('Marcando notificación como leída', ['notificacion_id' => $notificacionId]); */

            /* Obtiene el usuario en sesión mediante su ID */
            $usuario = Usuario::find(session('usuario_id'));

            /* Valida que el usuario exista */
            if (!$usuario) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            /* Busca la notificación dentro de las notificaciones del usuario */
            $notificacion = $usuario->notifications()->find($notificacionId);
            //Log::info('Notificación encontrada', ['notificacion' => $notificacion]);

            /* Valida la existencia de la notificación */
            if (!$notificacion) {
                return response()->json(['success' => false, 'message' => 'Notificación no encontrada'], 404);
            }

            /* Obtiene el contenido actual de la notificación y cambia el estado a inactivo */
            $data = $notificacion->data;
            //Log::info('datos de la notificacion', ['data' => $data]);
            if (isset($data['pertenece']) && $data['pertenece'] === 'cliente') {
                $data['es_asesor_activo'] = 0;
            }
            if (isset($data['pertenece']) && $data['pertenece'] === 'criterio') {
                $data['es_criterio'] = 0;
            }

            /* Actualiza el campo `data` en la notificación */
            $notificacion->update(['data' => $data]);

            /* Respuesta exitosa */
            return response()->json(['success' => true, 'message' => 'Notificación marcada como leída']);
        } catch (\Exception $e) {
            /* Manejo de errores inesperados */
            return response()->json(['success' => false, 'message' => 'Error al marcar notificación'], 500);
        }
    }
}
