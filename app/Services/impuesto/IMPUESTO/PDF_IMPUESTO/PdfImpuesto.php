<?php

namespace App\Services\impuesto\IMPUESTO\PDF_IMPUESTO;


use App\Models\impuesto\Tgi_padron;
use App\Models\impuesto\Tgi_carga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\impuesto\AGUA\PadronAguaService;
use App\Services\impuesto\TGI\PadronTgiService;
use App\Models\impuesto\Agua_padron;
use App\Models\impuesto\Agua_carga;
use Carbon\Carbon;

class PdfImpuesto
{

    //Este servicio se consume en la EXPORTACION DE PDF
    //Este servicio obtiene todos los registros por, año, mes y ordenado por folio separados por grupos de num_broche
    //  -- IMPORTANTE  --  SOLO ARMA BROCHE PARA REGISTROS QUE NO TENGAN FOLIOS 50000 EN ADELANTE
    //    Y QUE TENGAN ASIGNADO UN NUM_BROCHE DIFERENTE DE NULL
    //    Y QUE NO TENGAN BAJADO EN "S"
    public function obtenerRegistrosPorBroche($anio, $mes, $impuesto)
    {
        // 1️⃣ Traer los registros filtrados por año y mes
        $modelo = $impuesto === 'tgi' ? Tgi_carga::class : Agua_carga::class;
        $registros = $modelo::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->get();

        // 2️⃣ Filtrar registros que:
        //    - NO tengan folios >= 50000 en compartidos
        //    - Y tengan num_broche distinto de null
        $registrosFiltrados = $registros->filter(function ($registro) {
            if (is_null($registro->num_broche)) {
                return false; // Excluir si num_broche es null
            }
            if (isset($registro->bajado) && $registro->bajado === 'S') {
                return false; // Excluir si el registro está bajado en "S"
            }



            $compartidos = json_decode($registro->compartidos, true);
            if (!is_array($compartidos)) return true;

            foreach ($compartidos as $item) {
                if (isset($item['folio']) && $item['folio'] >= 50000 && $item['folio'] <= 59999) {
                    return false; // Excluir si está en el rango 50000–59999
                }
            }


            return true;
        });

        // 3️⃣ Agregar propiedad "folio" (usamos el primero válido)
        foreach ($registrosFiltrados as $registro) {
            $compartidos = json_decode($registro->compartidos, true);
            $folio = null;

            if (is_array($compartidos) && isset($compartidos[0]['folio'])) {
                $folio = $compartidos[0]['folio'];
            }

            $registro->folio = $folio ?? 0;
        }

        // 4️⃣ Ordenar los registros por folio
        $registrosOrdenados = $registrosFiltrados
            ->map(function ($registro) {
                // Decodificar el JSON de compartidos
                $compartidos = json_decode($registro['compartidos'], true);

                if (is_array($compartidos)) {
                    // Filtrar solo los activos
                    $activos = collect($compartidos)
                        ->where('estado', 'ACTIVO')
                        ->sortBy('folio')
                        ->values();


                    // Si hay activos, nos quedamos con el más chico
                    if ($activos->isNotEmpty()) {
                        $registro['compartidos'] = [$activos->first()];
                        $registro['folio'] = $activos->first()['folio']; // actualizar folio principal
                    } else {
                        // Si no hay activos, descartamos todos
                        $registro['compartidos'] = [];
                        $registro['folio'] = null;
                    }
                }

                return $registro;
            })
            ->filter(fn($registro) => !empty($registro['compartidos'])) // descartar los vacíos
            ->sortBy('folio')
            ->values();

        // 5️⃣ Agrupar por num_broche y sumar importes
        $grupos = [];

        foreach ($registrosOrdenados as $registro) {
            $numBroche = $registro->num_broche;

            if (!isset($grupos[$numBroche])) {
                $grupos[$numBroche] = [
                    'num_broche' => $numBroche,
                    'total' => 0,
                    'items' => []
                ];
            }

            $importe = floatval(str_replace(',', '.', $registro->importe));
            $grupos[$numBroche]['total'] += $importe;
            $grupos[$numBroche]['items'][] = $registro;
        }

        // 6️⃣ Ordenar los grupos por número de broche
        ksort($grupos);

        // 7️⃣ Calcular total general
        $totalGeneral = array_sum(array_column($grupos, 'total'));

        // 8️⃣ Retornar estructura final
        return [
            'total_general' => $totalGeneral,
            'broches' => array_values($grupos)
        ];
    }


    //Este servicio se consume en la EXPORTACION DE PDF
    //Este servicio obtiene todos los registros por, año, mes y ordenado por folio separados por grupos de num_broche
    //  -- IMPORTANTE  --  SOLO ARMA BROCHE PARA REGISTROS QUE TENGAN FOLIOS 50000 EN ADELANTE
    public function obtenerRegistrosDesdeFolio50000($anio, $mes, $impuesto)
    {
        // 1️⃣ Traer los registros filtrados por año y mes
        $modelo = $impuesto === 'tgi' ? Tgi_carga::class : Agua_carga::class;
        $registros = $modelo::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->get();


        // 2️⃣ Filtrar registros que tengan al menos un folio >= 50000
        $registrosFiltrados = $registros->filter(function ($registro) {
            $compartidos = json_decode($registro->compartidos, true);

            if (!is_array($compartidos)) return false;

            foreach ($compartidos as $item) {
                if (
                    isset($item['folio'], $item['estado']) &&
                    preg_match('/^500\d{2}$/', $item['folio']) &&   // folios 50000–50099
                    strtoupper($item['estado']) === 'ACTIVO'
                ) {
                    return true; // Incluir este registro
                }
            }


            return false; // Excluir si no tiene folios >= 50000
        });

        // 3️⃣ Agregar propiedad "folio" (usamos el primero que sea >= 50000)
        foreach ($registrosFiltrados as $registro) {
            $compartidos = json_decode($registro->compartidos, true);
            $folio = null;

            if (is_array($compartidos)) {
                foreach ($compartidos as $item) {
                    if (isset($item['folio']) && $item['folio'] >= 50000) {
                        $folio = $item['folio'];
                        break; // tomamos el primero que cumpla
                    }
                }
            }

            $registro->folio = $folio ?? 0;
        }

        // 4️⃣ Ordenar los registros por folio (de menor a mayor)
        $registrosOrdenados = $registrosFiltrados->sortBy('folio')->values();

        // 5️⃣ Agrupar por num_broche y sumar importes
        $grupos = [];

        foreach ($registrosOrdenados as $registro) {
            $numBroche = $registro->num_broche ?? 0;

            if (!isset($grupos[$numBroche])) {
                $grupos[$numBroche] = [
                    'num_broche' => $numBroche,
                    'total' => 0,
                    'items' => []
                ];
            }

            $importe = floatval(str_replace(',', '.', $registro->importe));
            $grupos[$numBroche]['total'] += $importe;
            $grupos[$numBroche]['items'][] = $registro;
        }

        // 6️⃣ Ordenar los grupos por número de broche
        ksort($grupos);

        // 7️⃣ Calcular total general
        $totalGeneral = array_sum(array_column($grupos, 'total'));

        // 8️⃣ Retornar estructura final
        return [
            'total_general' => $totalGeneral,
            'broches' => array_values($grupos)
        ];
    }
}
