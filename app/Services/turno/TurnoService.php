<?php

namespace App\Services\turno;


use App\Models\turnos\Turno;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make(
            $turno,
            [
                'sector_id' => 'required|integer',
                'numero_identificador' => 'required|numeric',
                'tipo_identificador' => 'required|string',
                'usuario' => 'nullable|integer'
            ],
            [
                'sector_id.required' => 'El sector es obligatorio',
                'sector_id.integer' => 'El sector debe ser un número válido',
                'numero_identificador.required' => 'El número identificador es obligatorio',
                'numero_identificador.numeric' => 'El número identificador debe ser un número',
                'tipo_identificador.required' => 'El tipo identificador es obligatorio',
                'tipo_identificador.string' => 'El tipo identificador debe ser texto',
                'usuario.integer' => 'El usuario debe ser un número válido'
            ]
        );

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $enviaTurno = Turno::create([
            'sector' => $turno['sector_id'],
            'numero_identificador' => $turno['numero_identificador'],
            'tipo_identificador' => $turno['tipo_identificador'],
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
