<?php

namespace App\Services\turno;


use App\Models\turnos\Turno;
use Illuminate\Support\Facades\Log;

class TurnoService
{
    
    public function getTurnosPendientes(){
        $turnos = Turno::where('activo', 1)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector')
            ->orderBy('fecha_carga', 'desc')
            ->get();
        return $turnos;
    }

    public function getTurnosLlamados(){
         $turnosLlamados = Turno::where('activo', 2)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector','usuario')
            ->orderBy('fecha_carga', 'desc')
            ->get();
        return $turnosLlamados;
    }

    public function getTurnosCompletados(){
         $turnosInactivos = Turno::where('activo', 0)
            ->whereDate('fecha_carga', now()->toDateString())
            ->with('sector','usuario')
            ->orderBy('fecha_carga', 'desc')
            ->get();
        return $turnosInactivos;
    }

    public function postCargarTurno($turno){
        Log::info($turno);  


        
        $enviaTurno = Turno::create([
            'sector' => $turno['sector_id'] ?? null,
            'numero_identificador' => $turno['numero_identificador'] ?? null,
            'tipo_identificador' => $turno['tipo_identificador'] ?? null,
            'usuario_id' => $turno['usuario'] ?? null,
            'fecha_carga' => now(),
            'activo' => 1
        ]);

        return $enviaTurno;
    }

    public function putLlamarTurno($id, $idUsuario = null){
        $turno = Turno::findOrFail($id);
        $turno->tomo_usuario_id = $idUsuario;
        $turno->fecha_llamado = now();
        $turno->activo = 2;
        $turno->save();
        return $turno;
    }

    public function putFinalizarTurno($turno){
        $turnoModel = Turno::findOrFail($turno);
        $turnoModel->activo = 0;
        $turnoModel->fecha_llamado = now();
        $turnoModel->save();
        return $turnoModel;
    }

    
}
