<?php

namespace App\Services\contable\sellado;

use App\Models\At_cl\Usuario;
use Illuminate\Support\Facades\DB;
use App\Models\Contable\Sellado\Registro_sellado;
use App\Services\contable\sellado\ValorGastoAdminitrativoService;
use App\Services\contable\sellado\ValorHojaService;
use App\Services\contable\sellado\ValorSelladoService;
use App\Services\contable\sellado\ValorDatosRegistralesService;
use Illuminate\Container\Attributes\Log;
use App\Models\contable\sellado\Valor_registro_extra;

class RegistroSelladoService
{

    public function __construct(
        protected ValorGastoAdminitrativoService $valorGastoAdminitrativo,
        protected ValorHojaService $valorHoja,
        protected ValorSelladoService $valorSellado,
        protected ValorDatosRegistralesService $valorDatosRegistrales,
        protected DatosCalculoService $datosCalculoService
    ) {}


    //Este metodo a parte de todos lo registros de sellado - se encarga de mostrar los datos de calculo
    public function getRegistroSellado(): array
    {
        return [
            'registros' => $this->getRegistroSelladoOrenados(),
            'configuracion' => [
                'valores_datos_registrales' => $this->datosCalculoService->getAllValorDatosRegistrales(),
                'valores_gasto_administrativo' => $this->datosCalculoService->getAllValorGastoAdministrativo(),
                'valores_hoja' => $this->datosCalculoService->getAllValorHoja(),
                'valores_sellado' => $this->datosCalculoService->getAllValorSellado(),
                'valor_registro_extra' => $this->datosCalculoService->getValorRegistroExtra(),
            ]
        ];
    }

    protected function getRegistroSelladoOrenados(): array{
        return Registro_sellado::orderBy('id_registro_sellado', 'desc')->get()->toArray();
    }



    public function calculoRegistroSelladoResultado(array $data)
    {
        $monto_documento = $data['monto_documento'] ?? 0;
        $monto_contrato = $data['monto_contrato'] ?? 0;
        $cantidad_meses = $data['cantidad_meses'] ?? 0;

        // Calcula los valores adicionales
        $gasto_administrativo = $this->calculateGastoAdministrativo(
            $data['monto_alquiler'],
            $monto_documento,
            $data['tipo_contrato'],
            $cantidad_meses
        );

        $prop_alquiler = $this->proporcional_alquiler(
            $data['monto_alquiler'],
            $monto_documento,
            $data['fecha_inicio']
        );

        $sellado = $this->sellado(
            $cantidad_meses,
            $data['monto_alquiler'],
            $data['tipo_contrato'],
            $data['hojas'],
            $data['inq_prop'],
            $monto_contrato
        );

        $valor_informe = $this->valor_informe(
            $data['informe'],
            $data['cantidad_informes'],
            $data['monto_alquiler'],
            $monto_documento
        );

        $monto_alquiler = $this->montoAlquilerComercialVivienda(
            $data['tipo_contrato'],
            $data['monto_alquiler']
        );
        $usuario_id =  session('usuario_id');

        // Retornar solo los cálculos sin guardar en la base de datos
        return [
            'folio'                 => $data['folio'],
            'nombre'                => $data['nombre'],
            'cantidad_meses'        => $data['cantidad_meses'],
            'monto_alquiler'        => $data['monto_alquiler'],
            'monto_documento'       => $data['monto_documento'],
            'monto_contrato'        => $data['monto_contrato'],
            'hojas'                 => $data['hojas'],
            'informe'               => $data['informe'],
            'cantidad_informes'     => $data['cantidad_informes'],
            'tipo_contrato'         => $data['tipo_contrato'],
            'inq_prop'              => $data['inq_prop'],
            'fecha_inicio'          => $data['fecha_inicio'],
            'gasto_administrativo'  => $gasto_administrativo['g_adm'],
            'prop_alquiler'         => $prop_alquiler['monto_alquiler'],
            'sellado'               => $sellado['total_sellado_con_hojas'],
            'valor_informe'         => $valor_informe,
            'iva_gasto_adm'         => $gasto_administrativo['iva_g_adm_o'],
            'monto_alquiler_comercial' => $monto_alquiler['monto_alquiler_comercial'],
            'monto_alquiler_vivienda'  => $monto_alquiler['monto_alquiler_vivienda'],
            'prop_doc'              => $prop_alquiler['monto_documento'],
            'total_contrato'        => $sellado['total_alquiler'],
            'fecha_carga'           => now()->toDateString(),
            'usuario_id'           => $usuario_id,

        ];
    }




    //En esta seccion se creara la logica para calcular el registro sellado
    public function calculateGastoAdministrativo($monto_alquiler, $monto_documento, $tipo_contrato, $meses)
    {
        $g_adm = 0;
        $valor_adm = $this->valorGastoAdminitrativo->getAllValorGastoAdministrativo();

        //dd(json_encode($valor_adm));

        if ($tipo_contrato == "Cochera") {
            if ($meses <= 6) {
                $g_adm = $valor_adm[0]->valor;
            } else {
                $g_adm = $valor_adm[1]->valor;
            }
        } else {
            $valor_multiplicar = $meses / 12;
            $g_adm = $monto_alquiler * $valor_multiplicar + $monto_documento * $valor_multiplicar;
            if ($g_adm != 0 && $g_adm < $valor_adm[2]->valor) {
                $g_adm = $valor_adm[2]->valor;
            }
        };

        $iva_g_adm_o = $g_adm * 0.21;

        return [
            "g_adm" => $g_adm,
            "iva_g_adm_o" => $iva_g_adm_o
        ];
    }

    public function proporcional_alquiler($monto_a, $monto_d, $fecha_i)
    {
        $dia_i = date('j', strtotime($fecha_i)); // Obtiene el día del mes

        if ($dia_i > 30) {
            $dia_i = 1;
        }

        $monto_alquiler = $monto_a / 30 * (31 - $dia_i);
        $monto_documento = $monto_d / 30 * (31 - $dia_i);

        //dd(json_encode($monto_alquiler));

        return [
            "monto_alquiler" => $monto_alquiler,
            "monto_documento" => $monto_documento
        ];
    }

    public function sellado($meses, $monto_a, $tipo_c, $hojas, $inq_prop, $monto_c)
    {
        $valor_hojas = $this->valorHoja->getAllValorHoja();
        $valor_tipos = $this->valorSellado->getAllValorSellado();


        $valor = 0;
        $total_alquiler = 0;
        $iva_total = $this->iva($inq_prop, $tipo_c, $monto_a);

        if ($tipo_c == "Vivienda" || $tipo_c == "Vivienda Comercial") {
            $valor = $valor_tipos[0]->valor;
        } else {
            $valor = $valor_tipos[1]->valor;
        }

        if ($monto_c <= 0) {
            $total_alquiler = $meses * $monto_a * $iva_total;
        } else {
            $total_alquiler = $monto_c * $iva_total;
        }


        $total_sellado = $total_alquiler * $valor / 100;
        $total_sellado_con_hojas = number_format($total_sellado + ($hojas * $valor_hojas), 2, '.', '');

        return [
            "total_sellado_con_hojas" => $total_sellado_con_hojas,
            "total_alquiler" => $total_alquiler
        ];
    }

    public function iva($inq_prop, $tipo_c, $monto_a)
    {
        if ($monto_a <= 1500 || $inq_prop == "SI" || $tipo_c == "Vivienda") {
            return 1;
        }
        return 1.21;
    }


    public function valor_informe($informe, $cantidad_informe, $monto_a, $monto_d)
    {
        $valor_registral = $this->valorDatosRegistrales->getAllValorDatosRegistrales();

        $monto_informe = 0;
        $a_sumar = 0;
        $monto = 0;

        $valor_registro_extra = Valor_registro_extra::all()->first()->valor_extra;

        if ($informe == "SI") {
            $monto = floatval($monto_a) + floatval($monto_d);

            if ($monto < $valor_registral[0]->valor_limite) {
                $monto_informe = $valor_registral[0]->precio;
            } else if ($monto < $valor_registral[1]->valor_limite) {
                $monto_informe = $valor_registral[1]->precio;
            } else {
                $monto_informe = $valor_registral[2]->precio;
            }
            if ($cantidad_informe < 3) {
                $a_sumar = 0;
            } else {
                /* aca deberia entrar el valor por base de datos */
                $x_sumar = $valor_registro_extra * ($cantidad_informe - 2);
                $a_sumar = $x_sumar;
            };
            $informe_total = floatval($monto_informe) + $a_sumar;
            //dd(json_encode($informe_total));
            return number_format($informe_total, 2, '.', '');
        } else {
            return number_format($monto_informe, 2, '.', '');
            //dd(json_encode($monto_informe));
        }
    }

    public function montoAlquilerComercialVivienda($tipo_c, $monto_alquiler)
    {
        $monto_alquiler_comercial = 0;
        $monto_alquiler_vivienda = 0;
        if ($tipo_c == "Vivienda") {
            $monto_alquiler_vivienda = $monto_alquiler;
        } else {
            $monto_alquiler_comercial = $monto_alquiler;
        }

        return [
            "monto_alquiler_vivienda" => $monto_alquiler_vivienda,
            "monto_alquiler_comercial" => $monto_alquiler_comercial
        ];
    }
}
