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
            return response()->json(['message' => 'Turno creado exitosamente', 'data' => $turno]);
        }  catch (\InvalidArgumentException $e) {
        // Error de validación - status 422 con mensaje específico
        Log::info($e->getMessage());
        return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function putLlamarTurno($id, Request $request)
    {
        try {
            $idUsuario = $request->input('id_usuario');
            $turno = (new TurnoService())->putLlamarTurno($id, $idUsuario);
            return response()->json($turno);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function finalizarturno($id)
    {
     
        try{
            $turno = (new TurnoService())->putFinalizarTurno($id);
            return response()->json($turno);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
