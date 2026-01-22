<?php

namespace App\Services\impuesto\TGI;

use App\Models\At_cl\Padron;
use App\Models\impuesto\Tgi_carga;
use App\Models\impuesto\Tgi_padron;
use Illuminate\Support\Facades\DB;
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

    //Este metodo obtiene todos los registros de la tabla tgi_carga ma
    public function obtenerRegistros()
    {
        return Tgi_carga::with('padron')->latest('id');
    }

    //Este metodo carga un nuevo registro de la tabla tgi_carga
    public function cargarNuevoTgiService($codigoBarras)
    {
        try {
            $listaCargaCompleta = $this->obtenerRegistros()->get();
            //dd($listaCargaCompleta->count());
            $cod_separado = $this->extraerCodBarra->separarCodigoBarras($codigoBarras);
            $fecha = Carbon::parse($cod_separado['fecha_vencimiento']);

            $tgi_padron = $this->padronTgiService->buscarTgiPorPartida($cod_separado['partida']);

            // Validación: ¿Existe el registro en ña tabla tgi_padrón para la partida dada?
            if (!$tgi_padron) {
                throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
            }

            //valida si el folio es compartido
            $foliosCompartidos = $this->detectarFoliosCompartidos($tgi_padron->partida, $tgi_padron->clave);

            // dd($foliosCompartidos); PASA

            // Validación: ¿Ya existe un registro con misma partida, mes y año?
            //dd($listaCargaCompleta);


            //dd($listaCargaCompleta);
            if ($this->yaFueCargado($listaCargaCompleta, $tgi_padron->id, $fecha->month, $fecha->year)) {
                throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha->month}/{$fecha->year}.");
            }



            $vencimientoContratos = $this->consultaVencimientoContratos($tgi_padron->folio, $tgi_padron->empresa);

            //dd($vencimientoContratos);

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

            //dd($registro);

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

    //Este metodo carga iun nuevo registro de la tabla tgi_carga manual
    public function cargarNuevoTgiServiceManual(Request $request)
    {
        try {
            $listaCargaCompleta = $this->obtenerRegistros()->get();
            $tgi_padron = $this->padronTgiService->buscarTgiPorPartida($request->partida);
            $fecha = Carbon::parse($request->fecha_vencimiento);

            //    dd($request->toArray());

            if (empty($request->importe) || $request->importe <= 0 || empty($request->fecha_vencimiento)) {
                throw new \Exception("Los campos importe y fecha de vencimiento son obligatorios y deben ser válidos.");
            }



            // Validación: ¿Existe el registro en ña tabla tgi_padrón para la partida dada?
            if (!$tgi_padron) {
                throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
            }

            //valida si el folio es compartido
            $foliosCompartidos = $this->detectarFoliosCompartidos($tgi_padron->partida, $tgi_padron->clave);

            if ($this->yaFueCargado($listaCargaCompleta, $tgi_padron->id, $fecha->month, $fecha->year)) {
                throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha->month}/{$fecha->year}.");
            }

            if ($tgi_padron->administra == "I" || $tgi_padron->administra == "P") {
                throw new \Exception("No se puede cargar un registro de tipo INQUILINO o PROPIETARIO.");
            }


            $vencimientoContratos = $this->consultaVencimientoContratos($tgi_padron->folio, $tgi_padron->empresa);


            $registro = [
                'codigo_barra' => null,
                'importe' => $request->importe,
                'compartidos' => json_encode($foliosCompartidos),
                'fecha_vencimiento' => $request->fecha_vencimiento,
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
            Log::error("Error en cargarNuevoTgiServiceManual: " . $e->getMessage());
            throw $e;
        }
    }


    public function eliminarRegistro($id)
    {
        $registro = Tgi_carga::find($id);
        $registro->delete();
    }




    //Este servicio modifica el estado (actvio/inactivo) de un registro en tgi_carga
    //Si se pasa a inactivo, se elimina el num_broche asignado
    //Si se pasa a activo, se debe GENERAR NUEVAMENTE EL BROCHE
    public function modificarEstado(int $id, string $estado)
    {
    
        $registro = Tgi_carga::findOrFail($id);

        $compartidos = json_decode($registro->compartidos ?? '[]', true);

        //log::info('Estado modificado para: ' . $id . ' a: ' . $estado);
        //Si el estado es INACTIVO, eliminar num_broche
        if ($estado === 'INACTIVO') {
            $registro->num_broche = null;
        }
        if (is_array($compartidos)) {
            foreach ($compartidos as &$item) {
                if (isset($item['folio'])) {
                    $item['estado'] = $estado;
                }
            }



            $registro->compartidos = json_encode($compartidos);
        }

        $registro->save();
    }



    //Este metodo exporta las tegi que faltan descargar, compara los folios exisentes en tgi_carga con los de tgi_padron
    public function exportarTgiFaltantesService($anio, $mes)
    {
        if (!is_numeric($anio) || !is_numeric($mes)) {
            throw new \InvalidArgumentException('Año y mes deben ser numéricos.');
        }

        // 1️⃣ Obtener registros cargados
        $foliosCargados = $this->obtenerFoliosCargados($anio, $mes); // array de objetos con folio, partida, clave

        Log::info('Folios cargados: ' . $foliosCargados);

        // 2️⃣ Obtener todos los registros activos que administra L
        $padrones = Tgi_padron::where('estado', 'ACTIVO')
            ->where('administra', 'L')
            ->get();



        // 3️⃣ Filtrar los que NO están en foliosCargados por folio + partida + clave.
        $faltantes = $padrones->filter(function ($padron) use ($foliosCargados) {
            foreach ($foliosCargados as $cargado) {
                if (

                    $padron->partida == $cargado->partida &&
                    $padron->clave == $cargado->clave
                ) {
                    return false; // ya está cargado
                }
            }
            return true; // no está cargado
        });

        return $faltantes; // limpia los índices
    }



    //Esta funcion arma los broches de la tabla tgi_carga
    public function armarBroches($anio, $mes)
    {
        if (!is_numeric($anio) || !is_numeric($mes)) {
            throw new \InvalidArgumentException('Año y mes deben ser numéricos.');
        }

        $listaCargaCompleta = $this->obtenerFoliosCargados($anio, $mes);
        dd($listaCargaCompleta);
    }


    //Este servicio suma los montos de los registros de tgi_carga para un mes y año determinados
    public function sumarMontosTgiService($anio, $mes)
    {
        // Obtener registros del período
        $registros = Tgi_carga::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->get();



        // Filtrar registros que:
        // - NO tengan folios 50xxx
        // - NO tengan todos los folios INACTIVOS
        // - Si baja esta en "S", NO se incluye
        $registrosFiltrados = $registros->filter(function ($registro) {
            $compartidos = json_decode($registro->compartidos, true);

            // Si no se puede decodificar, lo conservamos
            if (!is_array($compartidos)) return true;

            // Si tiene folio 50xxx, lo descartamos
            foreach ($compartidos as $comp) {
                if (
                    isset($comp['folio']) &&
                    preg_match('/^50\d{3}$/', $comp['folio'])
                ) {
                    return false;
                }
            }

            // Si el registro está bajado en "S", lo descartamos
            if (isset($registro->bajado) && $registro->bajado === 'S') {
                return false;
            }

            // Si todos los folios están INACTIVOS, lo descartamos
            $hayActivo = collect($compartidos)->contains(function ($c) {
                return strtoupper($c['estado']) === 'ACTIVO';
            });

            return $hayActivo;
        });

        log::info('Registros filtrados para sumar montos TGI: ', ['registros' => $registrosFiltrados]);

        // Calcular total sobre registros filtrados
        $total = $registrosFiltrados->sum(function ($r) {
            return (float) str_replace(',', '.', $r->importe);
        });

        // Retornar objeto con total y registros filtrados
        return (object)[
            'total' => $total,
            'registros' => $registrosFiltrados
        ];
    }



    //Este servicio suma los montos de los registros de tgi_carga solo los de SALAS para un mes y año determinados
    //Solo suma los registros que tengan folios 50xxx en compartidos
    //Si el registro está bajado en "S", lo descartamos
    public function sumarMontosTgiSalasService($anio, $mes)
    {
        $registros = Tgi_carga::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->get();


        $total = $registros->filter(function ($registro) {
            $compartidos = json_decode($registro->compartidos, true);

            if (!is_array($compartidos)) return false;

            foreach ($compartidos as $comp) {

                // Validamos folio y estado
                if (
                    isset($comp['folio'], $comp['estado']) &&
                    preg_match('/^50\d{3}$/', $comp['folio']) &&
                    strtoupper($comp['estado']) === 'ACTIVO'
                ) {
                    return true; // Incluir solo si hay folio 50xxx y está ACTIVO
                }
            }

            // Si el registro está bajado en "S", lo descartamos
            if (isset($registro->bajado) && $registro->bajado === 'S') {
                return false;
            }

            return false; // Excluir si no cumple condiciones
        })->sum(function ($r) {
            return (float) $r->importe;
        });


        return $total;
    }




    public function guardarNumBrocheService($anio, $mes, $cantidadBroches)
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

        /*  Log::info("Monto total: {$totalMontoBroche->total}");
        Log::info("Tope por broche: {$topePorBroche}");
        Log::info("Cantidad de registros: " . count($registrosFiltrados)); */

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

            // Log::info("Grupo folio {$folio} - Importe grupo: {$importeGrupo} - Registros: " . count($items));
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

            //  Log::info("Asignando grupo folio {$grupo['folio']} (importe: {$importeGrupo}) al broche " . ($brocheActual + 1));

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

            // Log::info("Broche {$numero} = \${$importeTotal} - Registros: " . count($broche['items']));
        }

        // Log::info("Suma total de broches: {$totalFinal}");

        // Paso 6: Verificar duplicados
        $idsAsignados = [];
        foreach ($broches as $broche) {
            foreach ($broche['items'] as $r) {
                if (in_array($r->id, $idsAsignados)) {
                    // Log::warning("Registro duplicado: ID {$r->id}");
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
    }



    //Este servicio sirve para enviar en formato JSON la info de los broches, y que sea consumia por el front JS
    public function generarDistribucionBroches($anio, $mes, $cantidadBroches)
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

        // Paso 1: Agrupar por folio mínimo ACTIVO
        $gruposPorFolio = [];

        foreach ($registrosFiltrados as $registro) {
            $folios = json_decode($registro->compartidos, true);
            //Log::info('Foliosssssss: ' . json_encode($registro));

            $foliosActivos = [];
            foreach ($folios as $f) {
                // Validar que exista estado y folio
                if (isset($f['estado'], $f['folio']) && strtoupper($f['estado']) === 'ACTIVO') {
                    $foliosActivos[] = (int)$f['folio'];
                }
            }

            // Si no hay folios activos, podés decidir qué hacer:
            if (empty($foliosActivos)) {
                //Log::warning("Registro {$registro->id} sin folios activos: " . json_encode($folios));
                continue; // lo excluyo, pero podés cambiar la lógica
            }

            // Tomar el folio activo más chico
            $folioMinimo = min($foliosActivos);

            if (!isset($gruposPorFolio[$folioMinimo])) {
                $gruposPorFolio[$folioMinimo] = [];
            }

            $gruposPorFolio[$folioMinimo][] = $registro;
        }

       // log::info('Grupos por folio: ' . json_encode($gruposPorFolio));

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

            //  Log::info("Grupo folio {$folio} - Importe grupo: {$importeGrupo} - Registros: " . count($items));
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

            /* ///////////////// */
           //Log::info("Asignando grupo folio {$grupo['folio']} (importe: {$importeGrupo}) al broche " . ($brocheActual + 1));

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

            Log::info("Broche {$numero} = \${$importeTotal} - Registros: " . count($broche['items']));
        }

        Log::info("Suma total de broches: {$totalFinal}");

        // Paso 6: Verificar duplicados
        $idsAsignados = [];
        foreach ($broches as $broche) {
            foreach ($broche['items'] as $r) {
                if (in_array($r->id, $idsAsignados)) {
                    Log::warning("Registro duplicado: ID {$r->id}");
                }
                $idsAsignados[] = $r->id;
            }
        }

        return [
            'broches' => $broches,
            'registrosFiltrados' => $registrosFiltrados,
            'total' => $totalFinal
        ];
    }

    public function guardarDistribucionBroches($registrosFiltrados)
    {
        try {
            foreach ($registrosFiltrados as $registro) {
                Tgi_carga::query()
                    ->where('id', $registro->id)
                    ->update(['num_broche' => $registro->num_broche]);
            }
            Log::info('Registros filtrados para guardar broches: ' . json_encode($registrosFiltrados));
        } catch (\Exception $e) {
            Log::error("Error al guardar broches: " . $e->getMessage());
            throw $e; // Propaga el error al controlador
        }
    }




    //Este servicio se consume en la EXPORTACION DE PDF
    //Este servicio obtiene todos los registros por, año, mes y ordenado por folio separados por grupos de num_broche
    //  -- IMPORTANTE  --  SOLO ARMA BROCHE PARA REGISTROS QUE NO TENGAN FOLIOS 50000 EN ADELANTE 
    //    Y QUE TENGAN ASIGNADO UN NUM_BROCHE DIFERENTE DE NULL
    //    Y QUE NO TENGAN BAJADO EN "S"
    public function obtenerRegistrosPorBroche($anio, $mes)
    {
        // 1️⃣ Traer los registros filtrados por año y mes
        $registros = Tgi_carga::where('periodo_anio', $anio)
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

        log::info('Registros ordenados para broches: ' . json_encode($registrosOrdenados));
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



    //BROCHES SALAS (FOLIOS 50000 EN ADELANTE)


    //Este servicio guarda el valor "salas" en la columna num_broche de tgi_carga
    // SOLO PARA REGISTROS QUE TENGAN FOLIOS 50000 EN ADELANTE
    //Si el registro está bajado en "S", lo descartamos
    public function guardarDistribucionBrocheSALAS($anio, $mes)
    {
        $registros = Tgi_carga::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->get();

        $registrosFiltrados = $registros->filter(function ($registro) {
            $compartidos = json_decode($registro->compartidos, true);

            if (!is_array($compartidos)) return false;

            if (isset($registro->bajado) && $registro->bajado === 'S') {
                return false;
            }

            foreach ($compartidos as $item) {
                if (
                    isset($item['folio']) &&
                    preg_match('/^500\d+$/', (string)$item['folio']) // ✅ solo folios que empiezan con 500
                ) {
                    return true;
                }
            }

            return false;
        });

        foreach ($registrosFiltrados as $registro) {
            Tgi_carga::where('id', $registro->id)
                ->update(['num_broche' => 'salas']);
        }

        return true;
    }


    //Este servicio se consume en la EXPORTACION DE PDF
    //Este servicio obtiene todos los registros por, año, mes y ordenado por folio separados por grupos de num_broche
    //  -- IMPORTANTE  --  SOLO ARMA BROCHE PARA REGISTROS QUE TENGAN FOLIOS 50000 EN ADELANTE
    public function obtenerRegistrosDesdeFolio50000($anio, $mes)
    {
        // 1️⃣ Traer los registros filtrados por año y mes
        $registros = Tgi_carga::where('periodo_anio', $anio)
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


















    //FUNCINES PRIVADAS QUE SE UTILIZAN SOLO EN ESTE SERVICIO

    //Esta funcion detecta los FOLIOS compartidos por partida, y clave
    private function detectarFoliosCompartidos($partida, $clave)
    {
        return Tgi_padron::where('partida', $partida)
            ->where('clave', $clave)
            ->get(['folio', 'estado'])
            ->map(function ($item) {
                return [
                    'folio' => $item->folio,
                    'estado' => $item->estado,
                ];
            })
            ->toArray();
    }




    //Esta función busca si ya se ha cargado un registro con el mismo padrón, mes y año (dentro de la tabla tgi_carga)
    private function yaFueCargado($lista, int $idPadron, int $mes, int $anio): bool
    {
        if ($lista->isEmpty()) {
            return false; // no hay nada, no hace nada
        }

        $registroExistente = $lista->first(function ($item) use ($idPadron, $mes, $anio) {
            return $item->id_tgiPadron == $idPadron &&
                $item->periodo_mes == $mes &&
                $item->periodo_anio == $anio;
        });

        //dd($registroExistente);
        return !is_null($registroExistente);
    }



    // Esta función obtiene la fecha de vencimiento de los contratos  para un folio y empresa dados
    private function consultaVencimientoContratos($folio, $id_empresa)
    {
        return Tgi_padron::where('folio', $folio)
            ->where('empresa', $id_empresa)
            ->select('comienza', 'rescicion', 'estado')
            ->get();
    }


    //Obtiene SOLO LOS FOLIOS cargados en tgi_carga para un mes y año determinados
    private function obtenerFoliosCargados($anio, $mes)
    {
        $jsonFolios = Tgi_carga::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->pluck('id_tgiPadron')
            ->toArray();

        //ahora tengo que conectar con la tabla tgi_padron mediante el id_tgiPadron para obtener los folios, clave y partida
        $padrones = Tgi_padron::whereIn('id', $jsonFolios)->get();
/* dd($padrones); */

        Log::info("Padrones: " . json_encode($padrones));

        return $padrones;
    }



    //Funcion que modifcia el bajado eN "S" de todos los registros de tgi_carga que tengan num_broche tenga un numero asignado y por anio y mes dados. Devueve men

    public function modificarBajadoService($anio, $mes)
    {

        log::info('Modificando bajado para anio: ' . $anio . ' mes: ' . $mes);
        $registros = Tgi_carga::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->whereNotNull('num_broche')
            ->get();

        log::info('Registros a modificar bajado: ' . $registros->count());
        foreach ($registros as $registro) {
            $registro->bajado = "S";
            $registro->save();
        }
    }
}
