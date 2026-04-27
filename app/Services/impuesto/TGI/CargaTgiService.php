<?php

namespace App\Services\impuesto\TGI;


use App\Models\impuesto\Tgi_carga;
use App\Services\impuesto\IMPUESTO\CargaImpuestoService;
use App\Services\impuesto\TGI\ExtraerCodBarra;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CargaTgiService
{
    protected $extraerCodBarra;
    protected $padronTgiService;


    public function __construct(ExtraerCodBarra $extraerCodBarra, PadronTgiService $padronTgiService)
    {
        $this->extraerCodBarra = $extraerCodBarra;
        $this->padronTgiService = $padronTgiService;
    }


    //Este metodo carga un nuevo registro de la tabla tgi_carga
    //Se deja porque el codigo de barra es diferente para cada impuesto
    public function cargarNuevoTgiService($codigoBarras)
    {
        try {
            $listaCargaCompleta = (new CargaImpuestoService)->obtenerRegistros('tgi')->get();

            $cod_separado = $this->extraerCodBarra->separarCodigoBarras($codigoBarras);
            $fecha = Carbon::parse($cod_separado['fecha_vencimiento']);

            $tgi_padron = $this->padronTgiService->buscarTgiPorPartida($cod_separado['partida']);

            // Validación: ¿Existe el registro en ña tabla tgi_padrón para la partida dada?
            if (!$tgi_padron) {
                throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
            }

            //valida si el folio es compartido
            $foliosCompartidos = (new CargaImpuestoService)->detectarFoliosCompartidos($tgi_padron->partida, $tgi_padron->clave, 'tgi');

            // Validación: ¿Ya existe un registro con misma partida, mes y año?

            if ((new CargaImpuestoService)->yaFueCargado($listaCargaCompleta, $tgi_padron->id, $fecha->month, $fecha->year, 'tgi')) {
                throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha->month}/{$fecha->year}.");
            }

            $vencimientoContratos = (new CargaImpuestoService)->consultaVencimientoContratos($tgi_padron->folio, $tgi_padron->empresa, 'tgi');

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
                'id_tgiPadron' => $tgi_padron->id,
            ];

            $nuevoRegistro = Tgi_carga::create($registro);

            if (!$nuevoRegistro) {
                throw new \Exception("Error al guardar el registro TGI");
            }

            return $nuevoRegistro;
        } catch (\Exception $e) {
            Log::error("Error en cargarNuevoTgiService: " . $e->getMessage());
            throw $e;
        }
    }

}
