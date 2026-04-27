<?php

namespace App\Services\impuesto\AGUA;


use App\Models\impuesto\Agua_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\impuesto\Agua_carga;
use App\Services\impuesto\AGUA\ExtraerCodBarraAgua;
use Carbon\Carbon;
use App\Services\impuesto\IMPUESTO\CargaImpuestoService;

class CargaAguaService
{

    //Este metodo carga un nuevo registro de la tabla tgi_carga
    public function cargarNuevoAguaService($codigoBarras)
    {
        //Log::info('llego al servicio');
        try {
            //Obtenemos todos los registros de la tabla carga
            $listaCargaCompleta = (new CargaImpuestoService())->obtenerRegistros('agua')->get();

            $cod_separado = (new ExtraerCodBarraAgua())->separarCodigoBarras($codigoBarras);
            $fecha_1 = Carbon::parse($cod_separado['fecha_vencimiento_1']);
            $fecha_2 = Carbon::parse($cod_separado['fecha_vencimiento_2']);

            $agua_padron = (new PadronAguaService())->buscarAguaPorPartida($cod_separado['partida']);

            // Validación: ¿Existe el registro en la tabla agua_padrón para la partida dada?
            if (!$agua_padron) {
                throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
            }

            //valida si el folio es compartido
            $foliosCompartidos = (new CargaImpuestoService())->detectarFoliosCompartidos($agua_padron->partida, $agua_padron->clave, 'agua');


            if ((new CargaImpuestoService())->yaFueCargado($listaCargaCompleta, $agua_padron->id, $fecha_1->month, $fecha_1->year, 'agua')) {
                throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha_1->month}/{$fecha_1->year}.");
            }

            //segunda validacion para la segunda cuota
            if ((new CargaImpuestoService())->yaFueCargado($listaCargaCompleta, $agua_padron->id, $fecha_2->month, $fecha_2->year, 'agua')) {
                throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha_2->month}/{$fecha_2->year}.");
            }



            $vencimientoContratos = (new CargaImpuestoService())->consultaVencimientoContratos($agua_padron->folio, $agua_padron->empresa, 'agua');



            $registro = [
                'codigo_barra' => $codigoBarras,
                'importe' => $cod_separado['importe_1'],
                'compartidos' => json_encode($foliosCompartidos),
                'fecha_vencimiento' => $cod_separado['fecha_vencimiento_1'],
                'periodo_anio' =>  $fecha_1->year,
                'periodo_mes' => $fecha_1->month,
                'num_broche' => null,
                'comienza' => $vencimientoContratos[0]->comienza,
                'rescicion' => $vencimientoContratos[0]->rescicion,
                'id_aguaPadron' => $agua_padron->id,
            ];


            $nuevoRegistro = Agua_carga::create($registro);

            $registro2 = [
                'codigo_barra' => $codigoBarras,
                'importe' => $cod_separado['importe_2'],
                'compartidos' => json_encode($foliosCompartidos),
                'fecha_vencimiento' => $cod_separado['fecha_vencimiento_2'],
                'periodo_anio' =>  $fecha_2->year,
                'periodo_mes' => $fecha_2->month,
                'num_broche' => null,
                'comienza' => $vencimientoContratos[0]->comienza,
                'rescicion' => $vencimientoContratos[0]->rescicion,
                'id_aguaPadron' => $agua_padron->id,
            ];


            $nuevoRegistro2 = Agua_carga::create($registro2);

            if (!$nuevoRegistro || !$nuevoRegistro2) {
                throw new \Exception("Error al guardar el registro AGUA");
            }

            return [$nuevoRegistro, $nuevoRegistro2];
        } catch (\Exception $e) {

            throw $e;
        }
    }
}
