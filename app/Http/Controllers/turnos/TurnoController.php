<?php

namespace App\Http\Controllers\turnos;

use App\Models\turnos\Turno;
use App\Models\turnos\Sector;
use Illuminate\Http\Request;
use App\Services\accessService;
use App\Services\turno\SectoresService;
use App\Services\turno\TurnoService;
use Exception;
use Illuminate\Support\Facades\Log;

class TurnoController
{


    public function getSectores()
    {
        try {
            $sectores = (new SectoresService())->getSectoresOrdenados();
            return response()->json($sectores);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getTurnosPendientes()
    {
        try {
            $turnos = (new TurnoService())->getTurnosPendientes();
            return response()->json($turnos);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTurnosLlamados()
    {
        try {
            $turnos = (new TurnoService())->getTurnosLlamados();
            return response()->json($turnos);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTurnosCompletados()
    {
        try {
            $turnos = (new TurnoService())->getTurnosCompletados();
            return response()->json($turnos);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function postCargarTurnoController(Request $request)
    {
        try {
            $turno = (new TurnoService())->postCargarTurno($request->all());
            return response()->json($turno);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function finalizarturno($id)
    {
        //Log::info('finalizarturno', ['id' => $id]);
        try{
            $turno = (new TurnoService())->putFinalizarTurno($id);
            return response()->json($turno);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
}
