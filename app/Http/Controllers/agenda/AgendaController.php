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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\usuarios_y_permisos\Usuario;


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

    // Método para guardar una nueva nota en la agenda
    public function store(Request $request)
    {
        Log::info('llego la informacion', $request->all());
        //dd($request->all());
        $cliente = null;

        DB::beginTransaction(); // Inicia la transacción

        try {


            // Buscar la agenda correspondiente al sector y usuario
            $agenda = Agenda::where('sector_id', $request->sector)
                ->where('usuario_id', $request->usuario)
                ->first();




            if (!$agenda) {
                // Si no se encuentra la agenda, redirigir con error
                return response()->json(['error' => 'No se encontró una agenda válida para este sector y usuario'], 404);
            }

            // Redondear horas de inicio y fin
            $horaInicioRedondeada = $request->horaInicio;
            $horaFinRedondeada = $request->horaFin;


            if (isset($request->propiedad['id'])) {
                $propiedadV = $request->propiedad['id'];
            } else {
                $propiedadV = null;
            }

            if ($request->telefono) {
                $cliente = Clientes::where('telefono', $request->telefono)->first();
                if (!$cliente) {
                    $cliente = Clientes::create([
                        'nombre' => $request->nombrecliente,
                        'telefono' => $request->telefono
                    ]);
                }
            }
            $usuario_id = auth('api')->id();


            // Crear la nota con horas redondeadas
            try {
                $nuevaHoraInicio = $horaInicioRedondeada . ':00';
                $repetida = Notas::where('fecha', $request->fecha)
                    ->where('hora_inicio', '<=', $nuevaHoraInicio)
                    ->where('hora_fin',    '>',  $nuevaHoraInicio)
                    ->where('usuario_id', $request->usuario)
                    ->where('agenda_id', $agenda->id)
                    ->where('activo', 1)
                    ->first();
                if ($repetida) {
                    Log::info('Ya existe una nota en ese horario');
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

            Log::info('Nota creada correctamente');
            // Si se proporciona un criterio, crear el evento en el historial
            if ($request->criterioSeleccionado != null) {
                if($propiedadV === null){
                    //Log::error('Propiedad no encontrada');
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Ingrese una propiedad'
                    ], 404);
                }
                Log::info('antes de buscar cliente');
                $busca_cliente = CriterioBusquedaVenta::where('id_criterio_venta', $request->criterioSeleccionado)->first();
                Log::info('despues de buscar cliente');
                // Buscar la propiedad correspondiente al sector y usuario
                $propiedadNC = Propiedad::where('id', $propiedadV)->first();
                Log::info('despues de buscar propiedad');
                // Buscar el nombre de la calle
                $calleName = Calle::where('id', $propiedadNC->id_calle)->first()->name;

                // Dia formato dd/mm/yyyy
                $fecha = Carbon::parse($request->fecha)->format('d/m/Y');

                Log::info('antes de crear historial');
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

                Log::info('despues de crear historial');
                $notas->update([
                    'cliente_id' => $busca_cliente->id_cliente
                ]);

                // Si es petición AJAX, responder con JSON
                return response()->json([
                    'success' => true,
                    //'evento' => $evento
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

    // Desactivar (eliminar lógicamente) una nota
    public function destroy($id, $motivo)
    {
        Log::info('entro');
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
            DB::rollBack(); // Revertir cambios ante error
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

    /* public function eventosDelDia()
    {
        $usuarioId = session('usuario_id');
        $hoy = now()->toDateString();

        $eventos = Notas::where('usuario_id', $usuarioId)
            ->whereDate('fecha', $hoy)
            ->get(['id', 'descripcion', 'hora_inicio', 'hora_fin', 'fecha', 'activo', 'realizado', 'cliente_id', 'devoluciones', 'propiedad_id', 'usuario_id']);

        return response()->json($eventos);
    } */

    public function buscarSectores()
    {
        return Sectores::select('id', 'nombre')->get();
    }


    public function traerUsuarioSector($id_sector, $fecha)
    {
        $agendas = Agenda::with([
            'usuario:id,username',

            // NOTAS DEL USUARIO → SOLO ACTIVAS
            'notas' => function ($q) use ($fecha) {
                $q->where('fecha', $fecha)
                    ->where('activo', 1)
                    ->with([
                        // 🔥 CLIENTE CON TODAS SUS NOTAS (SIN FILTRO)
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
                        'creado_por' => $item->creado_por,

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

                        //  TODAS LAS NOTAS DEL CLIENTE (activas + inactivas) ordenados por fecha mas reciente arriba
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
}
