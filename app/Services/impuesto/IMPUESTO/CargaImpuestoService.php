<?php

namespace App\Services\impuesto\IMPUESTO;


use App\Models\impuesto\Tgi_padron;
use App\Models\impuesto\Tgi_carga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\impuesto\AGUA\PadronAguaService;
use App\Services\impuesto\TGI\PadronTgiService;
use App\Models\impuesto\Agua_padron;
use App\Models\impuesto\Agua_carga;
use App\Models\impuesto\Gas_carga;
use App\Models\impuesto\Gas_padron;
use Carbon\Carbon;

class CargaImpuestoService
{

    public function obtenerRegistros($impuesto)
    {
        $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
        return $modelo::with('padron')->latest('id');
    }

    public function PadronCarga(Request $request)
    {

        // Recuperamos los filtros desde sesión si no vienen en el request
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $folio = $request->input('folio');
        $estado = $request->input('estado');
        $bajado = $request->input('bajado');
        $busqueda = $request->input('busqueda');
        $dia = $request->input('dia');

        $query = $this->obtenerRegistros($request->impuesto);

        $diaPresente = !is_null($dia) && $dia !== '';

        if ($request->impuesto === 'gas' && $diaPresente) {
            if (!$anio || !$mes) {
                return response()->json([
                    'error' => 'Para filtrar por día en gas, los campos anio y mes son obligatorios.'
                ], 422);
            }

            $fechaFiltro = Carbon::createFromDate((int) $anio, (int) $mes, (int) $dia)->format('Y-m-d');
            $query->whereDate('fecha_vencimiento', $fechaFiltro);
        } else {
            if ($anio) {
                $query->where('periodo_anio', $anio);
            }

            if ($mes) {
                $query->where('periodo_mes', $mes);
            }
        }



        if ($folio) {
            $query->where(function ($q) use ($folio) {
                // Buscar en padron
                $q->whereHas('padron', function ($sub) use ($folio) {
                    $sub->where('folio', $folio);
                });

                // Buscar dentro del JSON embebido en 'compartidos'
                $q->orWhere('compartidos', 'like', '%"folio":' . (int)$folio . '%');
            });
        }

        if ($estado) {
            $query->whereHas('padron', function ($sub) use ($estado) {
                $sub->where('estado', $estado);
            });
        }

        if ($bajado) {
            if ($bajado === 'N') {
                $query->where(function ($q) {
                    $q->whereNull('bajado')
                        ->orWhere('bajado', '=', 'N');
                });
            } else {
                $query->where(function ($q) {
                    $q->where('bajado', '=', 'S');
                });
            }
        }

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('padron', function ($sub) use ($busqueda) {
                    $sub->where('partida', 'like', "%{$busqueda}%")
                        ->orWhere('clave', 'like', "%{$busqueda}%");
                });

                // Si el campo compartidos es texto plano con JSON embebido
                $q->orWhere('compartidos', 'like', '%"partida":' . (int)$busqueda . '%');
            });
        }

        $registros = $query->get();

        return json_encode($registros);
    }

    //Este metodo obtiene el registro de la tabla tgi_padron filtrado por folio y empresa
    public function obtenerRegistroPadronManual($folio, $empresa, $impuesto)
    {
        Log::info('obtenerRegistroPadronManual', [
            'folio' => $folio,
            'empresa' => $empresa,
            'impuesto' => $impuesto
        ]);
        $modelo = $this->obtenerModeloPadronPorImpuesto($impuesto);
        $resultado = $modelo::where('folio', $folio)
            ->where('empresa', $empresa)
            ->get();
        Log::info('resultado', [$resultado]);

        return $resultado;
    }


    public function buscarImpuestoPorPartida($partida, $impuesto)
    {
        try {
            // Buscar en la tabla tgi_padron por el campo 'partida'
            $partida = trim($partida); // elimina espacios
            /* $modelo = $impuesto === 'tgi' ? Tgi_padron::class : Agua_padron::class; */

            $modelo = $this->obtenerModeloPadronPorImpuesto($impuesto);
            $tgiPadron = $modelo::where('partida', $partida)->first();

            return $tgiPadron; // Retorna el registro encontrado o null si no existe
        } catch (\Exception $e) {
            throw $e;
            Log::error('Error al buscar el impuesto por partida: ' . $e->getMessage());
        }
    }


    //Esta funcion detecta los FOLIOS compartidos por partida, y clave
    public function detectarFoliosCompartidos($partida, $clave, $impuesto)
    {
        $modelo = (new PadronImpuestoService)->obtenerModeloPorImpuesto($impuesto);
        return $modelo::where('partida', $partida)
            ->where('clave', $clave)
            ->get(['folio', 'estado', 'empresa'])
            ->map(function ($item) {
                return [
                    'folio' => $item->folio,
                    'estado' => $item->estado,
                    'empresa' => $item->empresa,
                ];
            })
            ->toArray();
    }

    //Esta función busca si ya se ha cargado un registro con el mismo padrón, mes y año (dentro de la tabla tgi_carga)
    public function yaFueCargado($lista, int $idPadron, int $mes, int $anio, string $impuesto): bool
    {
        if ($lista->isEmpty()) {
            return false; // no hay nada, no hace nada
        }


        if ($impuesto === 'tgi') {
            $registroExistente = $lista->first(function ($item) use ($idPadron, $mes, $anio) {
                return $item->id_tgiPadron == $idPadron &&
                    $item->periodo_mes == $mes &&
                    $item->periodo_anio == $anio;
            });
        }
        if ($impuesto === 'agua') {
            $registroExistente = $lista->first(function ($item) use ($idPadron, $mes, $anio) {
                return $item->id_aguaPadron == $idPadron &&
                    $item->periodo_mes == $mes &&
                    $item->periodo_anio == $anio;
            });
        }
        if ($impuesto === 'gas') {
            $registroExistente = $lista->first(function ($item) use ($idPadron, $mes, $anio) {
                return $item->id_gasPadron == $idPadron &&
                    $item->periodo_mes == $mes &&
                    $item->periodo_anio == $anio;
            });
        }

        return !is_null($registroExistente);
    }

    // Esta función obtiene la fecha de vencimiento de los contratos  para un folio y empresa dados
    public function consultaVencimientoContratos($folio, $id_empresa, $impuesto)
    {
        $modelo = (new PadronImpuestoService)->obtenerModeloPorImpuesto($impuesto);
        return $modelo::where('folio', $folio)
            ->where('empresa', $id_empresa)
            ->select('comienza', 'rescicion', 'estado')
            ->get();
    }

    //Este metodo carga un nuevo registro de la tabla tgi_carga manual
    public function cargarNuevoImpuestoManual(Request $request)
    {

        try {
            $listaCargaCompleta = $this->obtenerRegistros($request->impuesto)->get();
            Log::info('partida', [$request->partida]);
            $padron = $this->buscarImpuestoPorPartida($request->partida, $request->impuesto);
            $fecha = Carbon::parse($request->fecha_vencimiento);
            $fecha2 = Carbon::parse($request->fecha_vencimiento2);

            if (empty($request->importe) || $request->importe <= 0 || empty($request->fecha_vencimiento)) {
                throw new \Exception("Los campos importe y fecha de vencimiento son obligatorios y deben ser válidos.");
            }

            // Validación: ¿Existe el registro en ña tabla tgi_padrón para la partida dada?
            if (!$padron) {
                throw new \Exception('No existe un registro del padrón para el código de barras proporcionado.');
            }

            //valida si el folio es compartido
            $foliosCompartidos = $this->detectarFoliosCompartidos($padron->partida, $padron->clave, $request->impuesto);

            if ($this->yaFueCargado($listaCargaCompleta, $padron->id, $fecha->month, $fecha->year, $request->impuesto)) {
                throw new \Exception("Ya existe un registro cargado para esta partida en el periodo {$fecha->month}/{$fecha->year}.");
            }

            if ($padron->administra == "I" || $padron->administra == "P") {
                throw new \Exception("No se puede cargar un registro de tipo INQUILINO o PROPIETARIO.");
            }

            $vencimientoContratos = $this->consultaVencimientoContratos($padron->folio, $padron->empresa, $request->impuesto);

            if ($request->impuesto === 'tgi') {
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
                    'id_tgiPadron' => $padron->id,
                ];
                $nuevoRegistro = Tgi_carga::create($registro);
            }
            if ($request->impuesto === 'gas') {
                try {
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
                        'id_gasPadron' => $padron->id,
                    ];
                    $nuevoRegistro = Gas_carga::create($registro);
                } catch (\Exception $e) {
                    throw $e;
                    Log::error('Error al crear el registro de gas: ' . $e->getMessage());
                }
            }
            if ($request->impuesto === 'agua') {
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
                    'id_aguaPadron' => $padron->id,
                ];
                $nuevoRegistro = Agua_carga::create($registro);

                $registro2 = [
                    'codigo_barra' => null,
                    'importe' => $request->importe2,
                    'compartidos' => json_encode($foliosCompartidos),
                    'fecha_vencimiento' => $request->fecha_vencimiento2,
                    'periodo_anio' => $fecha2->year,
                    'periodo_mes' => $fecha2->month,
                    'num_broche' => null,
                    'comienza' => $vencimientoContratos[0]->comienza,
                    'rescicion' => $vencimientoContratos[0]->rescicion,
                    'id_aguaPadron' => $padron->id,
                ];
                $nuevoRegistro = Agua_carga::create($registro2);
            }



            if (!$nuevoRegistro) {
                throw new \Exception("Error al guardar el registro TGI");
            }
            return $nuevoRegistro;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //Obtiene SOLO LOS FOLIOS cargados en tgi_carga para un mes y año determinados
    private function obtenerFoliosCargados($anio, $mes, $impuesto)
    {
        if ($impuesto === 'tgi') {
            $jsonFolios = Tgi_carga::where('periodo_anio', $anio)
                ->where('periodo_mes', $mes)
                ->pluck('id_tgiPadron')
                ->toArray();

            //ahora tengo que conectar con la tabla tgi_padron mediante el id_tgiPadron para obtener los folios, clave y partida
            $padrones = Tgi_padron::whereIn('id', $jsonFolios)->get();
        }

        if ($impuesto === 'agua') {
            $jsonFolios = Agua_carga::where('periodo_anio', $anio)
                ->where('periodo_mes', $mes)
                ->pluck('id_aguaPadron')
                ->toArray();

            //ahora tengo que conectar con la tabla agua_padron mediante el id_aguaPadron para obtener los folios, clave y partida
            $padrones = Agua_padron::whereIn('id', $jsonFolios)->get();
        }

        if ($impuesto === 'gas') {
            $jsonFolios = Gas_carga::where('periodo_anio', $anio)
                ->where('periodo_mes', $mes)
                ->pluck('id_gasPadron')
                ->toArray();

            $padrones = Gas_padron::whereIn('id', $jsonFolios)->get();
        }



        return $padrones;
    }

    public function exportarFaltantesService($anio, $mes, $impuesto)
    {
        if (!is_numeric($anio) || !is_numeric($mes)) {
            throw new \Exception('Año y mes deben ser numéricos.');
        }

        // 1️⃣ Obtener registros cargados
        $foliosCargados = $this->obtenerFoliosCargados($anio, $mes, $impuesto); // array de objetos con folio, partida, clave

        // 2️⃣ Obtener todos los registros activos que administra L
        if ($impuesto === 'tgi') {
            $padrones = Tgi_padron::where('estado', 'ACTIVO')
                ->where('administra', 'L')
                ->get();
        }
        if ($impuesto === 'agua') {
            $padrones = Agua_padron::where('estado', 'ACTIVO')
                ->where('administra', 'L')
                ->get();
        }
        if ($impuesto === 'gas') {
            $padrones = Gas_padron::where('estado', 'ACTIVO')
                ->orWhere('estado', 'PENDIENTE')
                ->where('administra', 'L')
                ->get();
        }


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

    public function sumarMontosGasService($anio, $mes, $dia)
    {

        $fechaFiltro = Carbon::createFromDate((int) $anio, (int) $mes, (int) $dia)->format('Y-m-d');
        $registros = Gas_Carga::whereDate('fecha_vencimiento', $fechaFiltro)
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

        // Calcular total sobre registros filtrados
        /* $total = $registrosFiltrados->sum(function ($r) {
            return (float) str_replace(',', '.', $r->importe);
        }); */
        $total = round(
            $registrosFiltrados->sum(function ($r) {
                return (float) str_replace(',', '.', $r->importe);
            }),
            2 // cantidad de decimales
        );

        // Retornar objeto con total y registros filtrados
        return (object)[
            'total' => $total,
            'registros' => $registrosFiltrados
        ];
    }
    public function sumarMontosService($anio, $mes, $impuesto)
    {
        //Log::info('llego al service', [$anio, $mes, $impuesto]);
        // Obtener registros del período
        $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
        $registros = $modelo::where('periodo_anio', $anio)
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


    public function sumarMontosSalasService($anio, $mes, $impuesto)
    {
        //Log::info('llego al service', [$anio, $mes, $impuesto]);
        // Obtener registros del período
        $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
        $registros = $modelo::where('periodo_anio', $anio)
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

    public function generarDistribucionGasBroches($anio, $mes, $dia, $cantidadBroches)
    {
        $totalMontoBroche = $this->sumarMontosGasService($anio, $mes, $dia);

        $registros = $totalMontoBroche->registros;

        $topePorBroche = $totalMontoBroche->total / $cantidadBroches;


        //Log::info('dia', ['dia' => $dia]);
        //Filtrar registros del año y mes indicados
        $registrosFiltrados = [];

        foreach ($registros as $r) {
            // Convertimos la fecha a un timestamp para extraer sus partes
            $timestamp = strtotime($r->fecha_vencimiento);

            $vencimientoAnio = (int)date('Y', $timestamp);
            $vencimientoMes = (int)date('n', $timestamp); // 'n' devuelve el mes sin ceros iniciales (1-12)

            if ($vencimientoAnio === (int)$anio && $vencimientoMes === (int)$mes) {
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
                continue; // lo excluyo, pero podés cambiar la lógica
            }

            // Tomar el folio activo más chico
            $folioMinimo = min($foliosActivos);

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
                $registro->num_broche = 'Dia ' . $dia . ' - Broche N°' . ($brocheActual + 1);

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
                    Log::warning("Registro duplicado: ID {$r->id}");
                }
                $idsAsignados[] = $r->id;
            }
        }

        Log::info('broches', ['broches' => $broches]);
        Log::info('registrosFiltrados', ['registrosFiltrados' => $registrosFiltrados]);
        Log::info('total', ['total' => $totalFinal]);
        return [
            'broches' => $broches,
            'registrosFiltrados' => $registrosFiltrados,
            'total' => $totalFinal
        ];
    }
    //Este servicio sirve para enviar en formato JSON la info de los broches, y que sea consumia por el front JS
    public function generarDistribucionBroches($anio, $mes, $cantidadBroches, $impuesto)
    {
        // Paso 0: Calcular totales
        $totalMontoBroche = $this->sumarMontosService($anio, $mes, $impuesto);
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
                continue; // lo excluyo, pero podés cambiar la lógica
            }

            // Tomar el folio activo más chico
            $folioMinimo = min($foliosActivos);

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

    public function guardarDistribucionBroches($registrosFiltrados, $impuesto)
    {
        $usuarioId = auth('api')->id();
        try {
            foreach ($registrosFiltrados as $registro) {
                $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
                $modelo::query()
                    ->where('id', $registro->id)
                    ->update(['num_broche' => $registro->num_broche, 'armado' => $usuarioId]);
            }
        } catch (\Exception $e) {
            throw $e; // Propaga el error al controlador
        }
    }

    //Este servicio guarda el valor "salas" en la columna num_broche de tgi_carga
    // SOLO PARA REGISTROS QUE TENGAN FOLIOS 50000 EN ADELANTE
    //Si el registro está bajado en "S", lo descartamos
    public function guardarDistribucionBrocheSALAS($anio, $mes, $impuesto)
    {
        //Log::info('inicio servicio');
        $modelo = $impuesto === 'tgi' ? Tgi_carga::class : Agua_carga::class;
        $registros = $modelo::where('periodo_anio', $anio)
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
        //Log::info('medio servicio');
        try {
            $usuarioId = auth('api')->id();
            foreach ($registrosFiltrados as $registro) {
                $modelo::where('id', $registro->id)
                    ->update(['num_broche' => 'salas', 'controlado' => $usuarioId]);
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar num_broche: ' . $e->getMessage());
            throw $e;
        }
        //Log::info('fin servicio');
        return true;
    }

    //Funcion que modifcia el bajado eN "S" de todos los registros de tgi_carga que tengan num_broche tenga un numero asignado y por anio y mes dados. Devueve men
    public function modificarBajadoService($anio, $mes, $impuesto)
    {
        $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
        $registros = $modelo::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->whereNotNull('num_broche')
            ->get();


        foreach ($registros as $registro) {
            $registro->bajado = "S";
            $registro->save();
        }
    }

    //Este servicio modifica el estado (actvio/inactivo) de un registro en tgi_carga
    //Si se pasa a inactivo, se elimina el num_broche asignado
    //Si se pasa a activo, se debe GENERAR NUEVAMENTE EL BROCHE
    public function modificarEstado($id, $estado, $impuesto)
    {
        $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
        $registro = $modelo::findOrFail($id);

        $compartidos = json_decode($registro->compartidos ?? '[]', true);

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

    public function eliminarRegistro($id, $impuesto)
    {
        $modelo = $this->obtenerModeloCargaPorImpuesto($impuesto);
        $registro = $modelo::find($id);
        $registro->delete();
    }

    public function obtenerModeloCargaPorImpuesto($impuesto)
    {
        $map = [
            'tgi'  => Tgi_carga::class,
            'agua' => Agua_carga::class,
            'gas'  => Gas_carga::class,
        ];

        if (!isset($map[$impuesto])) {
            throw new \Exception("Impuesto no válido");
        }

        return $map[$impuesto];
    }

    public function obtenerModeloPadronPorImpuesto($impuesto)
    {
        $map = [
            'tgi'  => Tgi_padron::class,
            'agua' => Agua_padron::class,
            'gas'  => Gas_padron::class,
        ];

        if (!isset($map[$impuesto])) {
            throw new \Exception("Impuesto no válido");
        }

        return $map[$impuesto];
    }

    public function SinControlar()
    {
        $datos = Gas_carga::select('num_broche', 'fecha_vencimiento')
            ->where('bajado', 'N')
            ->distinct()
            ->get();

        Log::info($datos);
        return $datos;
    }

    public function gasRechazar($datos)
    {
        Gas_carga::where('fecha_vencimiento', $datos['fecha_vencimiento'])
            ->update(['num_broche' => null]);
        return response()->json(['message' => 'Gas rechazado correctamente'], 200);
    }

    public function gasBajado($datos)
    {
        $usuarioId = auth('api')->id();
        Gas_carga::where('fecha_vencimiento', $datos['fecha_vencimiento'])
            ->update(['bajado' => 'S', 'controlado' => $usuarioId]);
        return response()->json(['message' => 'Gas bajado correctamente'], 200);
    }
}
