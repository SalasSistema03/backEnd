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

    protected function getRegistroSelladoOrenados(): array
    {
        return Registro_sellado::orderBy('id_registro_sellado', 'desc')->get()->toArray();
    }


    public function calcularSellado(array $data)
    {
        // 1. Extraer valores con valores por defecto
        $monto_documento = $data['monto_documento'] ?? 0;
        $monto_contrato  = $data['monto_contrato'] ?? 0;
        $cantidad_meses  = $data['cantidad_meses'] ?? 0;
        $monto_alquiler  = $data['monto_alquiler'] ?? 0;

        // 2. Ejecutar cálculos internos
        $gasto_adm_calc = $this->calculateGastoAdministrativo(
            $monto_alquiler,
            $monto_documento,
            $data['tipo_contrato'],
            $cantidad_meses
        );

        $prop_alq_calc = $this->proporcional_alquiler(
            $monto_alquiler,
            $monto_documento,
            $data['fecha_inicio']
        );

        $sellado_calc = $this->sellado(
            $cantidad_meses,
            $monto_alquiler,
            $data['tipo_contrato'],
            $data['hojas'],
            $data['inq_prop'],
            $monto_contrato
        );

        $inf_calc = $this->valor_informe(
            $data['informe'],
            $data['cantidad_informes'],
            $monto_alquiler,
            $monto_documento
        );

        $monto_tipo_calc = $this->montoAlquilerComercialVivienda(
            $data['tipo_contrato'],
            $monto_alquiler
        );

        // 3. Retornar el array de resultados
        return [
            'folio'             => $data['folio'] ?? '',
            'nombre'            => $data['nombre'] ?? '',
            'cantidad_meses'    => $cantidad_meses,
            'monto_alquiler'    => $monto_alquiler,
            'monto_documento'   => $monto_documento,
            'monto_contrato'    => $monto_contrato,
            'hojas'             => $data['hojas'] ?? 0,
            'informe'           => $data['informe'] ?? 0,
            'cantidad_informes' => $data['cantidad_informes'] ?? 0,
            'tipo_contrato'     => $data['tipo_contrato'],
            'inq_prop'          => $data['inq_prop'],
            'fecha_inicio'      => $data['fecha_inicio'],
            'gasto_administrativo' => $gasto_adm_calc['g_adm'] ?? 0,
            'prop_alquiler'     => $prop_alq_calc['monto_alquiler'] ?? 0,
            'sellado'           => $sellado_calc['total_sellado_con_hojas'] ?? 0,
            'valor_informe'     => $inf_calc,
            'iva_gasto_adm'     => $gasto_adm_calc['iva_g_adm_o'] ?? 0,
            'monto_alquiler_comercial' => $monto_tipo_calc['monto_alquiler_comercial'] ?? 0,
            'monto_alquiler_vivienda'  => $monto_tipo_calc['monto_alquiler_vivienda'] ?? 0,
            'prop_doc'          => $prop_alq_calc['monto_documento'] ?? 0,
            'total_contrato'    => $sellado_calc['total_alquiler'] ?? 0,
            'fecha_carga'       => now()->toDateString(),
        ];
    }

    public function guardarSellado(array $data)
    {
        //Obtiene el id del usuario actual
        
        return DB::transaction(function () use ($data) {
            // Primero obtenemos los resultados del cálculo para estar seguros de qué guardamos
            $resultados = $this->calcularSellado($data);

            // Insertamos en la tabla (Ajusta los nombres de las columnas a tu BD)
            return Registro_sellado::create([
                'cantidad_informes'       => $resultados['cantidad_informes'],
                'cantidad_meses'          => $resultados['cantidad_meses'],
                'fecha_inicio'            => $resultados['fecha_inicio'],
                'folio'                   => $resultados['folio'],
                'gasto_administrativo'    => $resultados['gasto_administrativo'],
                'hojas'                   => $resultados['hojas'],
                'informe'                 => $resultados['informe'],
                'inq_prop'                => $resultados['inq_prop'],
                'iva_gasto_adm'           => $resultados['iva_gasto_adm'],
                'monto_alquiler_comercial' => $resultados['monto_alquiler_comercial'],
                'monto_alquiler_vivienda'  => $resultados['monto_alquiler_vivienda'],
                'monto_contrato'          => $resultados['monto_contrato'],
                'monto_documento'         => $resultados['monto_documento'],
                'nombre'                  => $resultados['nombre'],
                'prop_alquiler'           => $resultados['prop_alquiler'],
                'prop_doc'                => $resultados['prop_doc'],
                'sellado'                 => $resultados['sellado'],
                'tipo_contrato'           => $resultados['tipo_contrato'],
                'total_contrato'          => $resultados['total_contrato'],
                'valor_informe'           => $resultados['valor_informe'],
                'fecha_carga'             => $resultados['fecha_carga'],
                'usuario_id'              => session('usuario_id') ?? 1,
            ]);
        });
    }





    //En esta seccion se creara la logica para calcular el registro sellado
    protected function calculateGastoAdministrativo($monto_alquiler, $monto_documento, $tipo_contrato, $meses)
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

    protected function proporcional_alquiler($monto_a, $monto_d, $fecha_i)
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

    protected function sellado($meses, $monto_a, $tipo_c, $hojas, $inq_prop, $monto_c)
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

    protected function iva($inq_prop, $tipo_c, $monto_a)
    {
        if ($monto_a <= 1500 || $inq_prop == "SI" || $tipo_c == "Vivienda") {
            return 1;
        }
        return 1.21;
    }


    protected function valor_informe($informe, $cantidad_informe, $monto_a, $monto_d)
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

    protected function montoAlquilerComercialVivienda($tipo_c, $monto_alquiler)
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
