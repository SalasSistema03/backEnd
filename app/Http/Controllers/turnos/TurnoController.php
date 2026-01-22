<?php

namespace App\Http\Controllers\turnos;

use App\Models\turnos\Turno;
use App\Models\turnos\Sector;
use Illuminate\Http\Request;
use App\Services\accessService;


class TurnoController
{

    public function index()
    {
        $turnos = Turno::whereNull('fecha_llamado')->orderBy('created_at', 'asc')->get();
        return view('turnos.index', compact('turnos'));
    }

    public function create()
    {
        $usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $vistaNombre = 'turnos.create';
        // Crear una instancia del servicio de permisos
        $permisoService = new accessService($usuario_id);
        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }
        $sectores = Sector::all();
        $turnos = Turno::where('activo', 1)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector')
            ->orderBy('fecha_carga', 'desc')
            ->get();
        $turnosLlamados = Turno::where('activo', 2)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector')
            ->orderBy('fecha_carga', 'desc')
            ->get();
        $turnosInactivos = Turno::where('activo', 0)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector')
            ->orderBy('fecha_carga', 'desc')
            ->get();

        /* $turnos = Turno::whereNull('fecha_llamado')
        ->orderBy('fecha_carga')->get(); */
        $fecha_carga = now()->format('Y-m-d H:i:s'); // Fecha y hora actual
        return view('turnos.create', compact('turnos', 'sectores', 'fecha_carga', 'turnosLlamados', 'turnosInactivos'));
    }

    public function store(Request $request)
    {
        $usuario_id = session('usuario_id');
        $validated = $request->validate([
            'numero_identificador' => 'required|string',
            'tipo_identificador' => 'required|in:DNI,Folio',
            'sector' => 'required|string',
            'fecha_carga' => 'required|date'
        ]);
        
        // Agrega el usuario_id al array validado
        $validated['usuario_id'] = $usuario_id;
        Turno::create($validated);

        return redirect()->route('turnos.create')->with('success', 'Turno registrado exitosamente');
    }

    public function llamado(Request $request)
    {
        
        $sectores = Sector::all();
        $turnosPendientes = collect();
        $turnosInactivos2 = collect();
        $turnosInactivosFalse = collect();
        $sectorSeleccionado = $request->sector;
        if ($sectorSeleccionado) {
            $turnosPendientes = Turno::where('sector', $sectorSeleccionado)
                ->where('activo', true)
                ->whereNull('fecha_llamado')
                ->whereDate('fecha_carga', now()->toDateString())
                ->orderBy('fecha_carga')
                ->get();
            $turnosInactivos2 = Turno::where('sector', $sectorSeleccionado)
                ->where('activo', 2)
                ->whereDate('fecha_carga', now()->toDateString())
                ->orderBy('fecha_carga')
                ->get();
            $turnosInactivosFalse = Turno::where('sector', $sectorSeleccionado)
                ->where('activo', false)
                ->whereDate('fecha_carga', now()->toDateString())
                ->orderBy('fecha_carga')
                ->get();
        }
        // Obtener el último turno llamado por cada sector...
        $ultimosLlamados = [];
        foreach ($sectores as $sector) {
            $ultimo = Turno::where('sector', $sector->nombre)
                ->whereNotNull('fecha_llamado')
                ->whereDate('fecha_llamado', now()->toDateString())
                ->orderByDesc('fecha_llamado')
                ->first();
            $ultimosLlamados[$sector->nombre] = $ultimo;
        }

        $turnos = Turno::where('activo', 1)
            ->where('sector', $sectorSeleccionado)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector')
            ->orderBy('fecha_carga', 'desc')
            ->get();
        return view('turnos.llamado', compact('sectores', 'turnosPendientes', 'turnosInactivos2', 'turnosInactivosFalse', 'sectorSeleccionado', 'ultimosLlamados', 'turnos'));
    }




    public function llamar(Request $request)
    {
        $usuario_id = session('usuario_id');
        $tomo_usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $vistaNombre = 'turnos.llamar';
        // Crear una instancia del servicio de permisos
        $permisoService = new accessService($usuario_id);
        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }
        $turno = Turno::findOrFail($request->turno_id);
        $turno->tomo_usuario_id = $tomo_usuario_id;
        $turno->fecha_llamado = now();
        $turno->activo = 2;
        $turno->save();
        // Recarga la página con el sector seleccionado
        return redirect()->route('turnos.llamado', ['sector' => $turno->sector]);
    }

    public function verTurnosPendientesAllamar(Request $request)
    {
        $sectorSeleccionado = $request->get('sector');

        $turnosPendientes = collect();
        if ($sectorSeleccionado) {
            $turnosPendientes = Turno::where('sector', $sectorSeleccionado)
                ->where('activo', 1)
                ->whereNull('fecha_llamado')
                ->whereDate('fecha_carga', now()->toDateString())
                ->orderBy('fecha_carga')
                ->get();
        }

        return view('turnos.componentes._form_llamarTurnos', compact('turnosPendientes'));
    }


    public function mostrarTurnospendinatesAFinalizar(Request $request)
    {
        $usuario_id = session('usuario_id');
        $sectorSeleccionado = $request->get('sector');

        $turnosPendientes = collect();
        if ($sectorSeleccionado) {
            $turnosPendientes = Turno::where('activo', 2)
                ->where('sector', $sectorSeleccionado)
                ->whereDate('fecha_carga', now()->toDateString())
                ->orderBy('fecha_carga')
                ->get();
        }

        return view('turnos.componentes._form_llamarTurnosAFinalizar', compact('turnosPendientes'));
    }











    public function mostrar()
    {
        $usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $vistaNombre = 'turnos.mostrar';
        // Crear una instancia del servicio de permisos
        $permisoService = new accessService($usuario_id);
        // Verificar si el usuario tiene acceso a la vista
        if (!$permisoService->tieneAccesoAVista($vistaNombre)) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }
        $sectores = Sector::all();
        $turno = Turno::where('activo', 2)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector')
            ->orderBy('fecha_carga', 'desc') // descendente: el más nuevo primero
            ->get();

        return view('turnos.mostrar', compact('turno', 'sectores'));
    }

    public function finalizar($id)
    {
        $turno = Turno::findOrFail($id);
        $turno->activo = 0;
        $turno->fecha_llamado = now();
        /* $turno->usuario_id = auth()->id(); */
        $turno->save();
        return redirect()->route('turnos.llamado', ['sector' => $turno->sector])->with('success', 'Turno finalizado');
    }
}
