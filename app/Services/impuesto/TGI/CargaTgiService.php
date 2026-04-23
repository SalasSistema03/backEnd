<?php

namespace App\Services\impuesto\TGI;


use App\Models\impuesto\Tgi_carga;
use App\Models\impuesto\Tgi_padron;
use App\Services\impuesto\IMPUESTO\CargaImpuestoService;
use App\Services\impuesto\TGI\ExtraerCodBarra;
use Illuminate\Http\Request;
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

    public function ObtenerRegistrosPadron()
    {
        return Tgi_padron::with('padron')->latest('id');
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

    //Esta funcion arma los broches de la tabla tgi_carga
    // Posiblemente se use en el armado de pdf
    /* public function armarBroches($anio, $mes)
    {
        if (!is_numeric($anio) || !is_numeric($mes)) {
            throw new \InvalidArgumentException('Año y mes deben ser numéricos.');
        }

        $listaCargaCompleta = $this->obtenerFoliosCargados($anio, $mes);
    } */


    //*
    //NOTA* COMENTADO PORQUE NO SE SI SIRVE DE MOMENTO
    // Posiblemente se use en el armado de pdf
    //*
    /* public function guardarNumBrocheService($anio, $mes, $cantidadBroches)
    {
        // Paso 0: Calcular totales
        $totalMontoBroche = $this->sumarMontosTgiService($anio, $mes);
        $registros = $totalMontoBroche->registros;
        $topePorBroche = $totalMontoBroche->total / $cantidadBroches;

        // Filtrar registros del año y mes indicados
        $registrosFiltrados = [];
        foreach ($registros as $r) {
            if ((int)$r->periodo_anio === (int)$anio && (int)$r->periodo_mes === (int)$mes) {
                $registrosFiltrados[] = $r;
            }
        }

        // Paso 1: Agrupar por folio mínimo
        $gruposPorFolio = [];

        foreach ($registrosFiltrados as $registro) {
            $folios = json_decode($registro->compartidos, true);
            $foliosSolo = [];

            foreach ($folios as $f) {

                $foliosSolo[] = $f['folio'];
            }

            $folioMinimo = min($foliosSolo);

            if (!isset($gruposPorFolio[$folioMinimo])) {
                $gruposPorFolio[$folioMinimo] = [];
            }

            $gruposPorFolio[$folioMinimo][] = $registro;
        }

        // Paso 2: Armar grupos con suma de importes
        $grupos = [];
        foreach ($gruposPorFolio as $folio => $items) {
            $importeGrupo = 0;

            foreach ($items as $r) {
                $importeGrupo += (float) str_replace(',', '.', $r->importe);
            }

            $grupos[] = [
                'folio'  => $folio,
                'importe' => $importeGrupo,
                'items'   => $items
            ];
        }

        // Paso 3: Ordenar por folio ascendente
        usort($grupos, function ($a, $b) {
            return $a['folio'] <=> $b['folio'];
        });

        // Paso 4: Asignar secuencialmente a broches
        $broches = [];
        for ($i = 0; $i < $cantidadBroches; $i++) {
            $broches[$i] = [
                'importe' => 0,
                'items'   => []
            ];
        }

        $brocheActual = 0;
        $importeAcumulado = 0;

        foreach ($grupos as $grupo) {
            $importeGrupo = $grupo['importe'];

            // Si el broche actual ya llegó al tope, pasamos al siguiente
            if ($brocheActual < $cantidadBroches - 1 && $importeAcumulado >= $topePorBroche) {
                $brocheActual++;
                $importeAcumulado = 0;
            }

            foreach ($grupo['items'] as $registro) {
                $registro->num_broche = $brocheActual + 1;

                $importe = (float) str_replace(',', '.', $registro->importe);
                $broches[$brocheActual]['importe'] += $importe;
                $broches[$brocheActual]['items'][] = $registro;
            }

            $importeAcumulado += $importeGrupo;
        }

        // Paso 5: Verificar resumen por broche
        $totalFinal = 0;
        foreach ($broches as $i => $broche) {
            $numero = $i + 1;
            $importeTotal = number_format($broche['importe'], 2, ',', '.');
            $totalFinal += $broche['importe'];
        }

        // Paso 6: Verificar duplicados
        $idsAsignados = [];
        foreach ($broches as $broche) {
            foreach ($broche['items'] as $r) {
                if (in_array($r->id, $idsAsignados)) {
                }
                $idsAsignados[] = $r->id;
            }
        }

        // Paso 7: Guardar en la base
        foreach ($registrosFiltrados as $registro) {
            Tgi_carga::query()
                ->where('id', $registro->id)
                ->update(['num_broche' => $registro->num_broche]);
        }

        Log::info("Broches asignados secuencialmente por folio.");
    } */





}
