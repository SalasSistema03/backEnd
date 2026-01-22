<?php

namespace App\Services\contable\sellado;

use App\Models\Contable\Sellado\Registro_sellado;
use App\Models\Contable\Sellado\Valor_datos_registrales;
use App\Models\Contable\Sellado\Valor_gasto_administrativo;
use App\Models\Contable\Sellado\Valor_hoja;
use App\Models\Contable\Sellado\Valor_sellado;

class RegistroSelladoService
{
    //Variables de la tabla Valor_gasto_administrativo
    private $valor_gasto_administrativo;
    private $valor_hoja;
    private $valor_sellado;
    private $valor_registrales;



    // Variables a calcular
    private $gasto_administrativo;
    private $iva_gasto_adm;
    private $monto_alquiler_comercial;
    private $prop_alquiler;
    private $prop_doc;
    private $sellado;
    private $total_contrato;
    private $valor_informe;
    private $fecha_carga;
    private $monto_alquiler_vivienda;


    // Constructor corregido
    public function __construct() {
        // Obtener el primer registro de la tabla Valor_gasto_administrativo
        $this->valor_gasto_administrativo = Valor_gasto_administrativo::all();
        $this->valor_hoja = Valor_hoja::all();
        $this->valor_sellado = Valor_sellado::all();
        $this->valor_registrales = Valor_datos_registrales::all();
    }




    public function crearRegistro(array $data, $usuarioId){
        $registroSellado = new Registro_sellado();
        $registroSellado->usuario_id = $usuarioId;
        
        // Asignar campos desde el formulario
        $registroSellado->cantidad_meses = $data['cantidad_meses'];
        $registroSellado->fecha_inicio = $data['fecha_inicio'];
        $registroSellado->folio = $data['folio'];
        $registroSellado->hojas = $data['hojas'];
        $registroSellado->informe = $data['informe'];
        $registroSellado->inq_prop = $data['inq_prop'];
        $registroSellado->monto_alquiler = $data['monto_alquiler'];
        $registroSellado->monto_contrato = $data['monto_contrato'];
        $registroSellado->monto_documento = $data['monto_documento'];
        $registroSellado->nombre = $data['nombre'];
        $registroSellado->tipo_contrato = $data['tipo_contrato'];
        $registroSellado->cantidad_informes = $data['cantidad_informes'];

        // Verificar el valor obtenido
        //dd($this->valor_gasto_administrativo);

        // Guardar el registro en la base de datos
        // $registroSellado->save();

        return $this->valor_gasto_administrativo;
    }

    // Esta función calcula el gasto administrativo
    public function calcularGastoAdministrativo($monto_alquiler, $monto_documento, $tipo_contrato, $cantidad_meses){
        $gasto_administrativo = 0;
        /* if($tipo_contrato == 'Cochera'){

        } */
    }
}
