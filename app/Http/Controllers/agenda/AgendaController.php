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
                /* ->filter(fn($nota) => !empty($nota->propiedad?->cod_alquiler)) */
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

    // Buscar propiedades por código de alquiler o venta
    /* public function buscarPropiedadPorCodigo(Request $request)
    {
        $codigo = $request->input('codigo');

        // Se buscan propiedades con coincidencias en cod_alquiler o cod_venta
        $propiedades = Propiedad::with(['calle', 'zona', 'estadoVenta', 'estadoAlquiler'])
            ->where('cod_alquiler', 'like', "%{$codigo}%")
            ->orWhere('cod_venta', 'like', "%{$codigo}%")
            ->get();

        // Se transforma la colección para mostrar datos útiles
        $resultado = $propiedades->map(function ($prop) {

            //$tipo = $prop->cod_alquiler ? 'alquiler' : 'venta';
            //$codigo = $prop->cod_alquiler ?: $prop->cod_venta;
            $codigo_v = $prop->cod_venta;
            $codigo_a = $prop->cod_alquiler;
            //$tipo_v =  'venta'; *
            $dorm = $prop->cantidad_dormitorios;
            $banios = $prop->banios;
            $cochera = $prop->cochera;
            $estado_v = $prop->estadoVenta;
            $estado_a = $prop->estadoAlquiler ?? '-';

            return [
                'id' => $prop->id,
                //'codigo' => $codigo,

                'nombre_calle' => $prop->calle ? $prop->calle->name : null,
                'numero_calle' => $prop->numero_calle,
                'zona' => $prop->zona ? $prop->zona->name : null,

                'codigo_v' => $codigo_v,
                'codigo_a' => $codigo_a,
                'dorm' => $dorm,
                'banios' => $banios,
                'cochera' => $cochera,
                'estado_v' => $estado_v,
                'estado_a' => $estado_a,
            ];
        });

        return response()->json($resultado);
    } */

    // Página principal de la agenda deprecated
    /*  public function index(Request $request)
    {
        // Se obtiene lista de calles y números únicos para autocompletar
        $calles = Propiedad::with('calle')
            ->get()
            ->map(function ($prop) {
                return [
                    'nombre_calle' => $prop->calle ? $prop->calle->name : null,
                    'numero_calle' => $prop->numero_calle,
                    'id_inmueble' => $prop->id,
                    'cod_alquiler' => $prop->cod_alquiler,
                    'cod_venta' => $prop->cod_venta,
                ];
            })
            ->unique(function ($item) {
                return $item['nombre_calle'] . '_' . $item['numero_calle'] . '_' . $item['id_inmueble'] . '_' . $item['cod_alquiler'] . '_' . $item['cod_venta'];
            })
            ->values();


        $fechaSeleccionada = $request->get('fecha', date('Y-m-d'));

        //OBTIENE LOS SECTORES
        $sectores = Sectores::all();
        //OBTIENE LOS USUARIOS
        $usuariosAgenda = Agenda::all();

        // IDs de usuarios que tienen asignaciones en la agenda
        $usuarioIds = $usuariosAgenda->pluck('usuario_id')->unique();
        //OBTIENE LOS USUARIOS QUE CREARON LAS NOTAS
        $creadoPorIds = Notas::pluck('creado_por')->unique();
        //UNIEN LOS USUARIOS
        $allUserIds = $usuarioIds->merge($creadoPorIds)->unique();

        // Obtener nombres de usuarios desde una base externa (mysql4)
        $usernames = DB::connection('mysql4')
            ->table('usuarios')
            ->whereIn('id', $allUserIds)
            ->pluck('username', 'id');

        // Agregar nombre de usuario a cada entrada de la agenda
        foreach ($usuariosAgenda as $usuario) {
            $usuario->username = $usernames[$usuario->usuario_id] ?? 'Sin nombre';
        }

        //OBTIENE LA AGENDA
        $agenda = Agenda::all();

        // Notas activas para la fecha seleccionada
        $notas = Notas::with(['cliente', 'propiedad.calle'])
            ->whereDate('fecha', $fechaSeleccionada)
            ->where('activo', 1)
            ->get();

        // Agregar nombre del creador a cada nota
        foreach ($notas as $nota) {
            $nota->creado_por_username = $usernames[$nota->creado_por] ?? 'Sin nombre';
            //$id_cliente = $nota->cliente_id;
        }

        // IDs de los creadores de las notas
        $creadorIds = $notas->pluck('creado_por')->unique();
        //OBTIENE LOS USUARIOS QUE CREARON LAS NOTAS
        $creadores = DB::connection('mysql4')
            ->table('usuarios')
            ->whereIn('id', $creadorIds)
            ->pluck('username', 'id');

        // Organizar notas por usuario y hora
        $notasPorUsuarioHora = [];
        //ORGANIZA LAS NOTAS POR USUARIO Y HORA
        foreach ($notas as $nota) {
            $horaInicio = substr($nota->hora_inicio, 0, 5);
            $usuarioId = $nota->usuario_id;

            if (!isset($notasPorUsuarioHora[$usuarioId])) {
                $notasPorUsuarioHora[$usuarioId] = [];
            }

            $notasPorUsuarioHora[$usuarioId][$horaInicio] = $nota;
        }

        // Estructurar las notas por sector, usuario y hora
        $notasPorSectorUsuario = [];

        //ORGANIZA LAS NOTAS POR SECTOR, USUARIO Y HORA
        foreach ($sectores as $sector) {
            foreach ($usuariosAgenda->where('sector_id', $sector->id) as $usuario) {

                $usuarioId = $usuario->usuario_id;

                // Iteración por bloques de 15 minutos entre 7:00 y 20:00
                for ($h = 7; $h <= 20; $h++) {
                    for ($m = 0; $m < 60; $m += 15) {
                        $horaActual = sprintf('%02d:%02d', $h, $m);

                        if (isset($notasPorUsuarioHora[$usuarioId][$horaActual])) {
                            $nota = $notasPorUsuarioHora[$usuarioId][$horaActual];

                            $horaFin = substr($nota->hora_fin, 0, 5);

                            // Datos del cliente (si los hay)
                            $clienteData = $nota->cliente ? [
                                'cliente' => $nota->cliente->telefono . ' - ' . $nota->cliente->nombre,
                                'cliente_id' => $nota->cliente->id_cliente
                            ] : ['cliente' => '', 'cliente_id' => ''];

                            // Datos de la propiedad (si los hay)
                            $propiedadData = $nota->propiedad ? [
                                'propiedad' => ($nota->propiedad->cod_alquiler ?: $nota->propiedad->cod_venta) . ' - ' .
                                    ($nota->propiedad->calle ? $nota->propiedad->calle->name : '') . ' ' .
                                    $nota->propiedad->numero_calle,
                                'propiedad_id' => $nota->propiedad->id
                            ] : ['propiedad' => '', 'propiedad_id' => ''];

                            // Estructura final de visualización
                            if (!isset($notasPorSectorUsuario[$sector->id])) {
                                $notasPorSectorUsuario[$sector->id] = [];
                            }
                            if (!isset($notasPorSectorUsuario[$sector->id][$usuarioId])) {
                                $notasPorSectorUsuario[$sector->id][$usuarioId] = [];
                            }
                            //ORGANIZA LAS NOTAS POR SECTOR, USUARIO Y HORA
                            $notasPorSectorUsuario[$sector->id][$usuarioId][$horaActual] = array_merge([
                                'nota' => $nota,
                                'hora_inicio' => $horaActual,
                                'hora_fin' => $horaFin,
                            ], $clienteData, $propiedadData);
                        }
                    }
                }
            }
        }

        //dd($notasPorSectorUsuario);
        // Mostrar vista con todos los datos necesarios
        return view('agenda.index', compact(
            'sectores',
            'usuariosAgenda',
            'agenda',
            'notas',
            'fechaSeleccionada',
            'notasPorSectorUsuario',
            'creadores',
            'calles'
        ));
    } */

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

    /* public function marcarRealizada(Request $request)
    {
        // Inicia la transacción
        DB::beginTransaction();
        try {
            // Obtiene el ID de la nota desde el request
            $nota = Notas::findOrFail($request->input('nota_id'));
            // Marca la nota como realizada
            $nota->realizado = 1;
            // Guarda la nota
            $nota->save();
            // Confirmar transacción
            DB::commit();
            // Retorna una respuesta JSON con el éxito
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios ante error
            return response()->json(['error' => 'Error al marcar la nota como realizada'], 500);
        }
    } */

    /* public function buscarPropiedadPorCalle(Request $request)
    {

        try {
            $calle = $request->input('calle');
            // Si no se proporciona una calle o la calle es muy corta, retorna una respuesta vacía
            if (!$calle || strlen($calle) < 2) {
                return response()->json([]);
            }
            // Obtiene las propiedades que coinciden con la calle proporcionada
            $propiedades = Propiedad::whereHas('calle', function ($query) use ($calle) {
                $query->where('name', 'like', "%{$calle}%");
            })
                ->with(['calle', 'zona', 'estadoVenta', 'estadoAlquiler'])
                ->take(20)
                ->get();

            // Mapea las propiedades para obtener los datos necesarios
            $resultado = $propiedades->map(function ($prop) {

                try {
                    $tipo = $prop->cod_alquiler ? 'alquiler' : 'venta';
                    $codigo =  $prop->cod_venta ?? null;
                    $codigo_alquiler = $prop->cod_alquiler ?? null;

                    // Retorna los datos de la propiedad
                    return [
                        'id' => $prop->id,
                        'codigo' => $codigo,
                        'tipo' => $tipo,
                        'nombre_calle' => $prop->calle ? $prop->calle->name : null,
                        'numero_calle' => $prop->numero_calle,
                        'zona' => $prop->zona ? $prop->zona->name : null,
                        'tipo_v' => 'venta',
                        'codigo_v' => $prop->cod_venta,
                        'estado_venta' => $prop->estadoVenta ? $prop->estadoVenta->name : null,
                        'estado_alquiler' => $prop->estadoAlquiler ? $prop->estadoAlquiler->name : null,
                        'codigo_alquiler' => $codigo_alquiler,
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()->values();
            // Retorna los resultados
            return response()->json($resultado);
        } catch (\Exception $e) {
            //\Log::error("Error en buscarPropiedadPorCalle: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Error al buscar propiedades'], 500);
        }
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
