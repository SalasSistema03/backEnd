<?php

namespace App\Services\impuesto\GAS;


use App\Models\impuesto\Gas_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\impuesto\Gas_carga;
use App\Services\impuesto\GAS\ExtraerCodBarraGas;
use Carbon\Carbon;
use App\Services\impuesto\IMPUESTO\CargaImpuestoService;

class CargaGasService
{

    public function cargarNuevoGasService($codigoBarras)
    {
        //Obtenemos todos los registros de la tabla carga
        $listaCargaCompleta = (new CargaImpuestoService())->obtenerRegistros('gas')->get();

        $cod_separado = (new ExtraerCodBarraGas())->separarCodigoBarras($codigoBarras);

        $fecha = Carbon::parse($cod_separado['fecha_vencimiento']);

        $gas_padron = (new PadronGasService())->buscarGasPorPartida($cod_separado['partida']);

        // Validación: ¿Existe el registro en ña tabla tgi_padrón para la partida dada?
        if (!$gas_padron) {
            throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
        }

        //valida si el folio es compartido
        $foliosCompartidos = (new CargaImpuestoService)->detectarFoliosCompartidos($gas_padron->partida, $gas_padron->clave, 'gas');

        if ((new CargaImpuestoService)->yaFueCargado($listaCargaCompleta, $gas_padron->id, $fecha->month, $fecha->year, 'gas', $codigoBarras)) {
            throw new \Exception("Ya existe un registro con el mismo código de barra para esta partida en el periodo {$fecha->month}/{$fecha->year}.");
        }

        $vencimientoContratos = (new CargaImpuestoService)->consultaVencimientoContratos($gas_padron->folio, $gas_padron->empresa, 'gas');
        // Usar las fechas del registro ACTIVO del folio (no del MODIFICADO)
        $contratoActivo = $vencimientoContratos->firstWhere('estado', 'ACTIVO') ?? $vencimientoContratos->first();

        $registro = [
            'codigo_barra' => $codigoBarras,
            'importe' => $cod_separado['importe'],
            'compartidos' => json_encode($foliosCompartidos),
            'fecha_vencimiento' => $cod_separado['fecha_vencimiento'],
            'periodo_anio' => $fecha->year,
            'periodo_mes' => $fecha->month,
            'num_broche' => null,
            'comienza' => $contratoActivo->comienza,
            'rescicion' => $contratoActivo->rescicion,
            'id_gasPadron' => $gas_padron->id,
            'inicio_liquidacion' => $cod_separado['inicio_liquidacion'],
            'fin_liquidacion' => $cod_separado['fin_liquidacion'],
        ];

        $nuevoRegistro = Gas_carga::create($registro);
        if (!$nuevoRegistro) {
            throw new \Exception("Error al guardar el registro GAS");
        }

        return $nuevoRegistro;
    }
}
