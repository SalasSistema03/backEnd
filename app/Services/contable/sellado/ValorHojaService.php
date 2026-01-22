<?php
namespace App\Services\contable\sellado;

use App\Models\Contable\Sellado\Valor_hoja;

class ValorHojaService
{
    //Este funcion solo muestra el valor de la hoja
    public function getAllValorHoja(){
        $valor_sellado = Valor_hoja::all();
        return $valor_sellado[0]->precio;
    }

    // Método para actualizar el valor de la hoja
    public function modificarValoresgastoAdministrativo($data)
    {
        // $data es un array con las claves necesarias
        foreach ($data as $valor) {
            Valor_hoja::where('id_valor_hoja', $valor['id_valor_hoja'])
                ->update([
                    'precio' => $valor['precio'],
                ]);
        }
}                                   

}
