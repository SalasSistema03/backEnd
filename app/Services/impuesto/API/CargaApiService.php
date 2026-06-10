<?php

namespace App\Services\impuesto\API;


use App\Models\impuesto\Api_carga;
use App\Services\impuesto\IMPUESTO\CargaImpuestoService;
use App\Services\impuesto\API\ExtraerCodBarra;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CargaApiService
{
    protected $extraerCodBarra;
    protected $padronApiService;


    public function __construct(ExtraerCodBarra $extraerCodBarra, PadronAPIService $padronApiService)
    {
        $this->extraerCodBarra = $extraerCodBarra;
        $this->padronApiService = $padronApiService;
    }


    //Este metodo carga un nuevo registro de la tabla tgi_carga
    //Se deja porque el codigo de barra es diferente para cada impuesto
    public function cargarNuevoApiService($codigoBarras)
    {

        $listaCargaCompleta = (new CargaImpuestoService)->obtenerRegistros('api')->get();

        $cod_separado = $this->extraerCodBarra->separarCodigoBarras($codigoBarras);

        $fecha = Carbon::createFromFormat('y-m-d', $cod_separado['fecha_vencimiento']);

        $api_padron = $this->padronApiService->buscarApiPorPartida($cod_separado['partida']);

        // Validación: ¿Existe el registro en la tabla api_padron para la partida dada?
        if (!$api_padron) {
            throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
        }

        //valida si el folio es compartido
        $foliosCompartidos = (new CargaImpuestoService)->detectarFoliosCompartidos($api_padron->partida, $api_padron->clave, 'api');

        // Validación: ¿Ya existe un registro con misma partida, mes y año?
        if ((new CargaImpuestoService)->yaFueCargado($listaCargaCompleta, $api_padron->id, $fecha->month, $fecha->year, 'api')) {
            throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha->month}/{$fecha->year}.");
        }

        $vencimientoContratos = (new CargaImpuestoService)->consultaVencimientoContratos($api_padron->folio, $api_padron->empresa, 'api');

        $registro = [
            'codigo_barra' => $codigoBarras,
            'importe' => $cod_separado['importe'],
            'compartidos' => json_encode($foliosCompartidos),
            'fecha_vencimiento' => $cod_separado['fecha_vencimiento'],
            'periodo_anio' => $fecha->year,
            'periodo_mes' => $fecha->month,
            'num_broche' => null,
            'comienza' => $vencimientoContratos[0]->comienza,
            'rescicion' => $vencimientoContratos[0]->rescicion,
            'id_apiPadron' => $api_padron->id,
        ];

        $nuevoRegistro = Api_carga::create($registro);

        if (!$nuevoRegistro) {
            throw new \Exception("Error al guardar el registro API");
        }

        return $nuevoRegistro;
    }
}
