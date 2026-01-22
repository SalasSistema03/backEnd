<?php
namespace App\Services\contable\sellado;

use App\Models\Contable\Sellado\Valor_gasto_administrativo;

class ValorGastoAdminitrativoService
{
    public function getAllValorGastoAdministrativo(){
        $valor_gasto_administrativo = Valor_gasto_administrativo::all();
        return $valor_gasto_administrativo;
    }


     // Método para actualizar los valores de gasto administrativo
     public function modificarValoresgastoAdministrativo($data)
     {
         // $data es un array con las claves necesarias
         foreach ($data as $valor) {
             Valor_gasto_administrativo::where('id_valor_gasto_administrativo', $valor['id_valor_gasto_administrativo'])
                 ->update([
                     'tipo' => $valor['tipo'],
                     'valor' => $valor['valor'],
                 ]);
         }
     }

}
