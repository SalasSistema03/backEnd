<?php

namespace App\Services\contable\sellado;

use App\Models\contable\sellado\Valor_sellado;

class ValorSelladoService
{
    public function getAllValorSellado()
    {
        $valor_sellado = Valor_sellado::all();
        return $valor_sellado;
    }


    // Método para actualizar los valores del sellado
    public function modificarValoresgastoAdministrativo($data)
    {
        // $data es un array con las claves necesarias
        foreach ($data as $valor) {
            Valor_sellado::where('id_valor_sellado', $valor['id_valor_sellado'])
                ->update([
                    'tipo' => $valor['tipo'],
                    'valor' => $valor['valor'],
                ]);
        }
    }
}
