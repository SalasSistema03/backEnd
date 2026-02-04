<?php
namespace App\Services\contable\sellado;

use App\Models\Contable\Sellado\Valor_datos_registrales;

class ValorDatosRegistralesService
{
    public function getAllValorDatosRegistrales(){
        return Valor_datos_registrales::all();
    }

    // Método para actualizar los valores de los datos registrales
    public function modificarValoresRegistrales($data)
    {
        // $data es un array con las claves necesarias
        foreach ($data as $valor) {
            Valor_datos_registrales::where('id_valor_datos_registrales', $valor['id_valor_datos_registrales'])
                ->update([
                    'precio' => $valor['precio'],
                    'valor_limite' => $valor['valor_limite'],
                ]);
        }
    }

}
