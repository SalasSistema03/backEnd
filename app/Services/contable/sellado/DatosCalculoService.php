<?php
//Este servicio contiene los metodo para obtener/modificar los datos de calculo del sellado

namespace App\Services\contable\sellado;

use App\Models\Contable\Sellado\Valor_datos_registrales;
use App\Models\Contable\Sellado\Valor_gasto_administrativo;
use App\Models\Contable\Sellado\Valor_hoja;
use App\Models\contable\sellado\Valor_registro_extra;
use App\Models\Contable\Sellado\Valor_sellado;

class DatosCalculoService
{
    //Obtiene todos los valores de los datos registrales
    public function getAllValorDatosRegistrales()
    {
        return Valor_datos_registrales::all();
    }

    //Obtiene todos los valores de gasto administrativo
    public function getAllValorGastoAdministrativo()
    {
        return Valor_gasto_administrativo::all();
    }

    //Obtiene todos los valores de hoja
    public function getAllValorHoja()
    {
        return Valor_hoja::all();
    }

    //Obtiene todos los valores de sellado
    public function getAllValorSellado()
    {
        return Valor_sellado::all();
    }

    //Obtiene el valor del registro extra
    public function getValorRegistroExtra()
    {
        return Valor_registro_extra::first()->valor_extra ?? 0;
    }   


    // Método para actualizar los valores de los datos registrales
    public function setValoresRegistrales($data)
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


    // Método para actualizar los valores de los gastos administrativos
    public function setValoresgastoAdministrativo($data)
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

    // Método para actualizar los valores de hoja
    public function setValoresHoja($data)
    {
        // $data es un array con las claves necesarias
        foreach ($data as $valor) {
            Valor_hoja::where('id_valor_hoja', $valor['id_valor_hoja'])
                ->update([
                    'precio' => $valor['precio'],
                ]);
        }
    }

    // Método para actualizar los valores del sellado
    public function setValoresSellado($data)
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
