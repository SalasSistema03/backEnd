<?php

namespace App\Services\impuesto\IMPUESTO;


use App\Models\impuesto\Tgi_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\impuesto\AGUA\PadronAguaService;
use App\Services\impuesto\TGI\PadronTgiService;
use App\Models\impuesto\Agua_padron;
use App\Models\impuesto\Api_padron;
use App\Models\impuesto\boton_actualizado;
use App\Models\impuesto\Gas_padron;
use App\Services\impuesto\GAS\PadronGasService;
use App\Services\impuesto\API\PadronApiService;
use Carbon\Carbon;


class PadronImpuestoService
{

    //Esta funcion llama a la funcion obtenerPadronAGUA para obtener el padrón

    public function actualizarPadronImpuesto($impuesto, ?Carbon $periodo = null)
    {
        return $this->actualizarPadron($impuesto, $periodo);
    }

    public function actualizarPadron($impuesto, ?Carbon $periodo = null)
    {
        if ($impuesto === 'tgi' || $impuesto === 'agua' || $impuesto === 'api') {
            $this->obtenerPadron($impuesto, $periodo);
            return response()->json([
                'message' => 'Padrón ' . strtoupper($impuesto) . ' actualizado correctamente.',
                'ultimo_periodo' => $this->obtenerUltimoPeriodo($impuesto),
            ]);
        }
        if ($impuesto === 'gas') {
            $this->obtenerPadronServicio($impuesto);
            return response()->json([
                'message' => 'Padrón ' . strtoupper($impuesto) . ' actualizado correctamente.',
                'ultimo_periodo' => $this->obtenerUltimoPeriodo($impuesto),
            ]);
        }
    }

    public function obtenerUltimoPeriodo($impuesto)
    {
        $columna = [
            'tgi' => 'ultima_fecha_tgi',
            'agua' => 'ultima_fecha_agua',
            'gas' => 'ultima_fecha_gas',
            'api' => 'ultima_fecha_api',
        ][$impuesto] ?? null;

        if (!$columna) {
            return null;
        }

        return boton_actualizado::query()->value($columna);
    }

    private function calcularEstadoGas($rescicion, $hoy)
    {
        // Sin fecha de rescisión → INACTIVO directo
        if (empty($rescicion) || $rescicion === '0000-00-00') {
            return 'INACTIVO';
        }

        try {
            $fechaRescision = \Carbon\Carbon::parse($rescicion);

            // Solo trabajamos con meses, NUNCA con días
            $mesActual    = ($hoy->year * 12) + $hoy->month;
            $mesRescision = ($fechaRescision->year * 12) + $fechaRescision->month;

            $diferenciaMeses = $mesActual - $mesRescision;

            // Rescisión futura o mes actual → ACTIVO
            if ($diferenciaMeses <= 0) {
                return 'ACTIVO';
            }

            // 1 o 2 meses de diferencia → PENDIENTE
            if ($diferenciaMeses <= 2) {
                return 'PENDIENTE';
            }

            // 3+ meses → INACTIVO (incluye registros del 2022, 2023, etc.)
            return 'INACTIVO';
        } catch (\Exception $e) {
            return 'INACTIVO';
        }
    }

    public function obtenerPadronServicio($impuesto)
    {
        if ($impuesto !== 'gas') return [];

        $padronExistente = Gas_padron::all();
        $nuevoPadron     = (new PadronGasService)->consultaObtenerPadronGAS();

        // ── Índices ─────────────────────────────────────────────
        $existentePorFolioPartida = collect($padronExistente)->mapWithKeys(
            fn($item) => [$item->folio . '-' . $item->partida => $item]
        );

        $nuevoPorFolioPartida = collect($nuevoPadron)->mapWithKeys(
            fn($item) => [$item->folio . '-' . $item->partida => $item]
        );

        // Agrupados por folio+calle para detectar modificados
        $existentePorFolioCalle = collect($padronExistente)->groupBy(
            fn($item) => $item->folio . '-' . trim($item->calle)
        );

        $nuevoPorFolioCalle = collect($nuevoPadron)->groupBy(
            fn($item) => $item->folio . '-' . trim($item->calle)
        );

        $hoy = now();

        $nuevosRegistros    = collect();
        $actualizadosEstado = collect();
        $modificados        = collect();

        // ── 0. Detectar MODIFICADOS ─────────────────────────────
        foreach ($existentePorFolioCalle as $folioCalleKey => $registrosViejos) {

            // Si no hay registros nuevos con esta calle, no hay modificados aquí
            if (!$nuevoPorFolioCalle->has($folioCalleKey)) {
                continue;
            }

            $registrosNuevos = $nuevoPorFolioCalle[$folioCalleKey];

            foreach ($registrosViejos as $viejo) {

                // Los MODIFICADOS ya son históricos, no se vuelven a tocar
                if ($viejo->estado === 'MODIFICADO') {
                    continue;
                }

                $partidaViejaExisteEnNuevos = $registrosNuevos->contains(
                    fn($nuevo) => $nuevo->partida === $viejo->partida
                );

                // CASO A: partida cambió (mismo folio+calle, distinta partida)
                if (!$partidaViejaExisteEnNuevos) {
                    Gas_padron::where('folio', $viejo->folio)
                        ->where('partida', $viejo->partida)
                        ->update(['estado' => 'MODIFICADO']);

                    $modificados->push([
                        'folio'   => $viejo->folio,
                        'partida' => $viejo->partida,
                        'calle'   => $viejo->calle,
                        'motivo'  => 'cambio de partida',
                    ]);
                    continue;
                }

                // CASO B: partida coincide pero clave cambió
                $nuevoCorrespondiente = $registrosNuevos->first(
                    fn($nuevo) => $nuevo->partida === $viejo->partida
                );

                if ($nuevoCorrespondiente) {
                    $claveVieja = is_numeric($viejo->clave) ? (string) $viejo->clave : '';
                    $claveNueva = is_numeric($nuevoCorrespondiente->clave) ? (string) $nuevoCorrespondiente->clave : '';

                    if ($claveVieja !== $claveNueva) {
                        Gas_padron::where('folio', $viejo->folio)
                            ->where('partida', $viejo->partida)
                            ->update(['estado' => 'MODIFICADO']);

                        $modificados->push([
                            'folio'   => $viejo->folio,
                            'partida' => $viejo->partida,
                            'calle'   => $viejo->calle,
                            'motivo'  => 'cambio de clave',
                        ]);
                    }
                }
            }
        }

        // ── 1. Sync total (nuevos + actualizaciones) ────────────
        foreach ($nuevoPorFolioPartida as $key => $registro) {

            $estadoCalculado   = $this->calcularEstadoGas($registro->rescicion, $hoy);
            $registroExistente = $existentePorFolioPartida[$key] ?? null;

            $data = [
                'calle'      => !empty($registro->calle)
                    ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8')
                    : '',
                'clave'      => is_numeric($registro->clave) ? $registro->clave : null,
                'abona'      => $registro->abona      ?? '',
                'administra' => $registro->administra ?? '',
                'empresa'    => $registro->id_empresa,
                'comienza'   => $registro->comienza   ?? 0,
                'rescicion'  => $registro->rescicion  ?? null,
                'estado'     => $estadoCalculado,
            ];

            if ($registroExistente) {
                // No pisar registros MODIFICADOS (son históricos)
                if ($registroExistente->estado === 'MODIFICADO') {
                    continue;
                }

                if ($registroExistente->estado !== $estadoCalculado) {
                    $actualizadosEstado->push([
                        'folio'           => $registro->folio,
                        'partida'         => $registro->partida,
                        'estado_anterior' => $registroExistente->estado,
                        'estado_nuevo'    => $estadoCalculado,
                        'rescicion'       => $registro->rescicion,
                    ]);
                }

                Gas_padron::where('folio', $registro->folio)
                    ->where('partida', $registro->partida)
                    ->update($data);
            } else {
                $data['folio']   = is_numeric($registro->folio) ? $registro->folio : null;
                $data['partida'] = $registro->partida ?? '';

                Gas_padron::create($data);
                $nuevosRegistros->push($registro);
            }
        }

        // ── 2. Inactivar los que ya no están en el padrón nuevo ─
        $modificadosKeys = Gas_padron::where('estado', 'MODIFICADO')
            ->get(['folio', 'partida'])
            ->map(fn($r) => $r->folio . '-' . $r->partida)
            ->toArray();

        $posiblesInactivos = $existentePorFolioPartida
            ->diffKeys($nuevoPorFolioPartida)
            ->filter(fn($registro, $key) => !in_array($key, $modificadosKeys));

        foreach ($posiblesInactivos as $registro) {
            $nuevoEstado = $this->calcularEstadoGas($registro->rescicion, $hoy);

            if ($registro->estado !== $nuevoEstado) {
                Gas_padron::where('folio', $registro->folio)
                    ->where('partida', $registro->partida)
                    ->update(['estado' => $nuevoEstado]);

                $actualizadosEstado->push([
                    'folio'           => $registro->folio,
                    'partida'         => $registro->partida,
                    'estado_anterior' => $registro->estado,
                    'estado_nuevo'    => $nuevoEstado,
                    'rescicion'       => $registro->rescicion,
                ]);
            }
        }

        $fechagas = boton_actualizado::query()->firstOrFail()->ultima_fecha_gas;

        boton_actualizado::query()->update([
            'ultima_fecha_gas' => Carbon::parse($fechagas)
                ->addMonthNoOverflow()
                ->toDateString(),
        ]);

        return [
            'nuevos'              => $nuevosRegistros->values(),
            'actualizados_estado' => $actualizadosEstado->values(),
            'modificados'         => $modificados->values(),
        ];
    }


    public function obtenerPadron($impuesto, ?Carbon $periodo = null)
    {
        if ($impuesto === 'tgi') {
            $padronExistente = Tgi_padron::all();
            $periodoConsulta = $periodo?->copy()->startOfMonth() ?? Carbon::now()->startOfMonth();
            $nuevoPadron = (new PadronTgiService)->consultaObtenerPadronTGI($periodoConsulta);

            $fechaTgi = boton_actualizado::query()->firstOrFail()->ultima_fecha_tgi;

            boton_actualizado::query()->update([
                'ultima_fecha_tgi' => Carbon::parse($fechaTgi)
                    ->addMonthNoOverflow()
                    ->toDateString(),
            ]);
        }

        if ($impuesto === 'agua') {
            //Log::info('entro a impuesto agua');
            $padronExistente = Agua_padron::all();
            $nuevoPadron = (new PadronAguaService)->consultaObtenerPadronAgua();

            $fechagua = boton_actualizado::query()->firstOrFail()->ultima_fecha_agua;

            boton_actualizado::query()->update([
                'ultima_fecha_agua' => Carbon::parse($fechagua)
                    ->addMonthNoOverflow()
                    ->toDateString(),
            ]);
        }

        if ($impuesto === 'api') {
            $padronExistente = Api_padron::all();
            $periodoConsulta = $periodo?->copy()->startOfMonth() ?? Carbon::now()->startOfMonth();
            $nuevoPadron = (new PadronApiService)->consultaObtenerPadronAPI($periodoConsulta);

            $fechapi = boton_actualizado::query()->firstOrFail()->ultima_fecha_api;

            boton_actualizado::query()->update([
                'ultima_fecha_api' => Carbon::parse($fechapi)
                    ->addMonthsNoOverflow(2)
                    ->toDateString(),
            ]);
        }

        $modelo = $this->obtenerModeloPorImpuesto($impuesto);
        $padronExistente = collect($padronExistente);
        $nuevoPadron = collect($nuevoPadron);

        $this->vincularCabecerasExistentes($modelo, $padronExistente, $nuevoPadron);

        // ── Índices para búsqueda rápida ──────────────────────────────────────
        $registrosExistentesPorIdentidad = $padronExistente->groupBy(
            fn($item) => $this->claveRegistroPadron($item, $impuesto)
        );
        $existentePorFolioPartida = $registrosExistentesPorIdentidad->map(
            fn($registros) => $registros->first()
        );
        $nuevoPorFolioPartida = $nuevoPadron->mapWithKeys(
            fn($item) => [$this->claveRegistroPadron($item, $impuesto) => $item]
        );


        $existentePorFolioCalle = $padronExistente->groupBy(function ($item) {
            return $item->folio . '-' . trim($item->calle);
        });

        $nuevoPorFolioCalle = $nuevoPadron->groupBy(function ($item) {
            return $item->folio . '-' . trim($item->calle);
        });

        // ── 0. Reactivar INACTIVOS ──────────────────────────────────────────
        $reactivar = $nuevoPorFolioPartida->filter(function ($registro, $key) use ($existentePorFolioPartida) {
            return isset($existentePorFolioPartida[$key]) && $existentePorFolioPartida[$key]->estado === 'INACTIVO';
        });

        foreach ($reactivar as $key => $registro) {
            $this->actualizarRegistrosPorIdentidad($modelo, $registrosExistentesPorIdentidad, $key, [
                'estado'                => 'ACTIVO',
                'calle'                 => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                'clave'                 => is_numeric($registro->clave) ? $registro->clave : null,
                'abona'                 => $registro->abona ?? '',
                'administra'            => $registro->administra ?? '',
                'empresa'               => $registro->empresa ?? 0,
                'id_contrato_cabecera'  => $registro->id_contrato_cabecera ?? null,
                'comienza'              => $registro->comienza ?? 0,
                'rescicion'             => $registro->rescicion ?? 0,
            ]);
        }

        // ── 0.5 Detectar MODIFICADOS ────────────────────────────────────────
        foreach ($existentePorFolioCalle as $folioCalleKey => $registrosViejos) {

            if (!$nuevoPorFolioCalle->has($folioCalleKey)) {
                continue;
            }

            $registrosNuevos = $nuevoPorFolioCalle[$folioCalleKey];

            foreach ($registrosViejos as $viejo) {

                // Los MODIFICADOS ya son históricos, no se vuelven a tocar
                if ($viejo->estado === 'MODIFICADO') {
                    continue;
                }

                $registrosMismaCabecera = $registrosNuevos->filter(
                    fn($nuevo) => $this->esMismaCabeceraContrato($viejo, $nuevo)
                );

                if ($registrosMismaCabecera->isEmpty()) {
                    continue;
                }

                $partidaViejaLimpia = ltrim($viejo->partida, '0');
                $partidaViejaNumerica = is_numeric($partidaViejaLimpia) && !empty($partidaViejaLimpia);

                if ($impuesto === 'api') {
                    // ── API: folio+calle fijos, partida variable ──
                    $partidaViejaExisteEnNuevos = $registrosMismaCabecera->contains(function ($nuevo) use ($viejo) {
                        return $this->normalizarPartida($nuevo->partida) === $this->normalizarPartida($viejo->partida);
                    });

                    if (!$partidaViejaExisteEnNuevos) {
                        $this->actualizarRegistrosPorIdentidad(
                            $modelo,
                            $registrosExistentesPorIdentidad,
                            $this->claveRegistroPadron($viejo, $impuesto),
                            ['estado' => 'MODIFICADO']
                        );
                    }
                } else {
                    // ── TGI / Agua / Gas ──

                    // CASO B: partida vieja no numérica → llegan nuevos con partida numérica real
                    if (!$partidaViejaNumerica) {
                        $hayNuevosConPartidaReal = $registrosMismaCabecera->contains(function ($nuevo) {
                            $partidaNuevaLimpia = ltrim($nuevo->partida, '0');
                            return is_numeric($partidaNuevaLimpia) && !empty($partidaNuevaLimpia);
                        });

                        if ($hayNuevosConPartidaReal) {
                            $this->actualizarRegistrosPorIdentidad(
                                $modelo,
                                $registrosExistentesPorIdentidad,
                                $this->claveRegistroPadron($viejo, $impuesto),
                                ['estado' => 'MODIFICADO']
                            );
                        }
                        continue;
                    }

                    // CASO A: partida numérica vieja no existe entre los nuevos (cambió la partida)
                    $partidaViejaExisteEnNuevos = $registrosMismaCabecera->contains(function ($nuevo) use ($viejo) {
                        return $this->normalizarPartida($nuevo->partida) === $this->normalizarPartida($viejo->partida);
                    });

                    if (!$partidaViejaExisteEnNuevos) {
                        // La partida cambió → MODIFICADO
                        $this->actualizarRegistrosPorIdentidad(
                            $modelo,
                            $registrosExistentesPorIdentidad,
                            $this->claveRegistroPadron($viejo, $impuesto),
                            ['estado' => 'MODIFICADO']
                        );
                        continue;
                    }

                    // CASO C: partida coincide pero clave cambió
                    $nuevoCorrespondiente = $registrosMismaCabecera->first(function ($nuevo) use ($viejo) {
                        return $this->normalizarPartida($nuevo->partida) === $this->normalizarPartida($viejo->partida);
                    });

                    if ($nuevoCorrespondiente) {
                        $claveVieja = is_numeric($viejo->clave) ? (string) $viejo->clave : '';
                        $claveNueva = is_numeric($nuevoCorrespondiente->clave) ? (string) $nuevoCorrespondiente->clave : '';

                        if ($claveVieja !== $claveNueva) {
                            $this->actualizarRegistrosPorIdentidad(
                                $modelo,
                                $registrosExistentesPorIdentidad,
                                $this->claveRegistroPadron($viejo, $impuesto),
                                ['estado' => 'MODIFICADO']
                            );
                        }
                    }
                }
            }
        }

        // ── 1. Nuevos registros ─────────────────────────────────────────────
        $nuevosRegistros = $nuevoPorFolioPartida->diffKeys($existentePorFolioPartida);

        // ── 2. Sync total ───────────────────────────────────────────────────
        foreach ($nuevoPorFolioPartida as $key => $registro) {
            $registroExistente = $existentePorFolioPartida[$key] ?? null;

            if ($registroExistente) {
                // No pisar registros MODIFICADOS (son históricos)
                if ($registroExistente->estado === 'MODIFICADO') {
                    continue;
                }

                $this->actualizarRegistrosPorIdentidad($modelo, $registrosExistentesPorIdentidad, $key, [
                    'estado'               => 'ACTIVO',
                    'calle'                => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                    'clave'                => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona'                => $registro->abona ?? '',
                    'administra'           => $registro->administra ?? '',
                    'empresa'              => $registro->empresa ?? 0,
                    'id_contrato_cabecera' => $registro->id_contrato_cabecera ?? null,
                    'comienza'             => $registro->comienza ?? 0,
                    'rescicion'            => $registro->rescicion ?? 0,
                ]);
            } else {
                $modelo::create([
                    'folio'      => is_numeric($registro->folio) ? $registro->folio : null,
                    'calle'      => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                    'partida'    => $registro->partida ?? '',
                    'clave'      => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona'      => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa'    => $registro->empresa ?? 0,
                    'id_contrato_cabecera' => $registro->id_contrato_cabecera ?? null,
                    'estado'     => 'ACTIVO',
                    'comienza'   => $registro->comienza ?? 0,
                    'rescicion'  => $registro->rescicion ?? 0,
                ]);
            }
        }

        // ── 3. Inactivar / Pendiente ────────────────────────────────────────
        // Refrescar desde BD los registros que ya fueron marcados MODIFICADO
        $modificadosEnBD = $modelo::where('estado', 'MODIFICADO')
            ->get(['id', 'folio', 'empresa', 'partida', 'clave', 'id_contrato_cabecera', 'comienza'])
            ->map(fn($registro) => $this->claveRegistroPadron($registro, $impuesto))
            ->toArray();

        $posiblesInactivos = $existentePorFolioPartida
            ->diffKeys($nuevoPorFolioPartida)
            ->filter(fn($registro, $key) => !in_array($key, $modificadosEnBD, true));

        if ($periodo !== null && in_array($impuesto, ['tgi', 'api'], true)) {
            $mesReferencia = ($periodo->year * 12) + $periodo->month;
            $posiblesInactivos = $posiblesInactivos->filter(function ($registro) use ($mesReferencia) {
                if (empty($registro->rescicion)) {
                    return true;
                }

                $fechaRescision = Carbon::parse($registro->rescicion);
                $mesRescision = ($fechaRescision->year * 12) + $fechaRescision->month;

                return $mesRescision <= $mesReferencia;
            });
        }

        $fechaReferencia = $periodo?->copy()->startOfMonth() ?? Carbon::now();
        $registrosInactivos = $posiblesInactivos->map(function ($registro) use ($impuesto, $fechaReferencia) {

            if (empty($registro->rescicion)) {
                $registro->nuevo_estado = 'INACTIVO';
                return $registro;
            }

            if ($impuesto === 'tgi') {
                $registro->nuevo_estado = $this->calcularEstadoTgiAusente(
                    $registro->rescicion,
                    $fechaReferencia
                );
                return $registro;
            }

            if ($impuesto === 'api') {
                $registro->nuevo_estado = $this->calcularEstadoApiAusente(
                    $registro->rescicion,
                    $fechaReferencia
                );
                return $registro;
            }

            $fechaRescision = \Carbon\Carbon::parse($registro->rescicion);
            $mesActual       = ($fechaReferencia->year * 12) + $fechaReferencia->month;
            $mesRescision    = ($fechaRescision->year * 12) + $fechaRescision->month;
            $diferenciaMeses = $mesActual - $mesRescision;

            if ($impuesto === 'agua') {
                $registro->nuevo_estado = $diferenciaMeses >= 3 ? 'INACTIVO' : 'PENDIENTE';
                return $registro;
            }

            return $registro;
        });

        // Update final estados
        foreach ($registrosInactivos as $registro) {
            if (!isset($registro->nuevo_estado)) {
                continue;
            }

            $this->actualizarRegistrosPorIdentidad(
                $modelo,
                $registrosExistentesPorIdentidad,
                $this->claveRegistroPadron($registro, $impuesto),
                ['estado' => $registro->nuevo_estado]
            );
        }

        return [
            'nuevos'    => $nuevosRegistros->values(),
            'inactivos' => $registrosInactivos->values(),
        ];
    }

    private function vincularCabecerasExistentes($modelo, $padronExistente, $nuevoPadron): void
    {
        $nuevosPorInicioContrato = $nuevoPadron->groupBy(
            fn($registro) => $this->claveFolioPartidaComienzo($registro)
        );

        foreach ($padronExistente as $existente) {
            if ($existente->id_contrato_cabecera !== null && $existente->id_contrato_cabecera !== '') {
                continue;
            }

            $candidatos = $nuevosPorInicioContrato[$this->claveFolioPartidaComienzo($existente)] ?? collect();
            $idsCabecera = $candidatos
                ->pluck('id_contrato_cabecera')
                ->filter(fn($id) => $id !== null && $id !== '')
                ->unique()
                ->values();

            if ($idsCabecera->count() !== 1) {
                continue;
            }

            $idCabecera = $idsCabecera->first();
            $modelo::whereKey($existente->getKey())
                ->update(['id_contrato_cabecera' => $idCabecera]);
            $existente->id_contrato_cabecera = $idCabecera;
        }
    }

    private function claveRegistroPadron($registro, ?string $impuesto = null): string
    {
        $idCabecera = $registro->id_contrato_cabecera ?? null;
        $identidadCabecera = $idCabecera !== null && $idCabecera !== ''
            ? 'cabecera:' . $idCabecera
            : 'comienza:' . $this->normalizarFechaPadron($registro->comienza ?? null);

        $identidad = [
            (string) ($registro->folio ?? ''),
            (string) ($registro->empresa ?? ''),
            $this->normalizarPartida($registro->partida ?? ''),
        ];

        if ($impuesto !== 'api') {
            $identidad[] = (string) ($registro->clave ?? '');
        }

        $identidad[] = $identidadCabecera;

        return implode('|', $identidad);
    }

    private function claveFolioPartidaComienzo($registro): string
    {
        return implode('|', [
            (string) ($registro->folio ?? ''),
            (string) ($registro->empresa ?? ''),
            $this->normalizarPartida($registro->partida ?? ''),
            $this->normalizarFechaPadron($registro->comienza ?? null),
        ]);
    }

    private function normalizarPartida($partida): string
    {
        return ltrim(trim((string) $partida), '0');
    }

    private function normalizarFechaPadron($fecha): string
    {
        if ($fecha === null || $fecha === '' || $fecha === 0 || $fecha === '0' || $fecha === '0000-00-00') {
            return '';
        }

        try {
            return Carbon::parse($fecha)->toDateString();
        } catch (\Exception) {
            return (string) $fecha;
        }
    }

    private function calcularEstadoTgiAusente($rescicion, Carbon $fechaActual): string
    {
        $fechaRescision = Carbon::parse($rescicion);
        $mesActual = ($fechaActual->year * 12) + $fechaActual->month;
        $mesRescision = ($fechaRescision->year * 12) + $fechaRescision->month;
        $diferenciaMeses = $mesActual - $mesRescision;

        if ($diferenciaMeses < 0 || $diferenciaMeses > 2) {
            return 'INACTIVO';
        }

        if (
            $diferenciaMeses === 1
            || $diferenciaMeses === 2
            || (
                $diferenciaMeses === 0
                && $fechaRescision->lt($fechaActual->copy()->startOfDay())
            )
        ) {
            return 'PENDIENTE';
        }

        return 'ACTIVO';
    }

    private function calcularEstadoApiAusente($rescicion, Carbon $fechaReferencia): string
    {
        $fechaRescision = Carbon::parse($rescicion);
        $mesReferencia = ($fechaReferencia->year * 12) + $fechaReferencia->month;
        $mesRescision = ($fechaRescision->year * 12) + $fechaRescision->month;
        $diferenciaMeses = $mesReferencia - $mesRescision;

        if ($diferenciaMeses >= 4) {
            return 'INACTIVO';
        }

        if ($diferenciaMeses >= 1) {
            return 'PENDIENTE';
        }

        return 'ACTIVO';
    }

    private function esMismaCabeceraContrato($existente, $nuevo): bool
    {
        $idExistente = $existente->id_contrato_cabecera ?? null;
        $idNuevo = $nuevo->id_contrato_cabecera ?? null;

        if ($idExistente !== null && $idExistente !== '' && $idNuevo !== null && $idNuevo !== '') {
            return (string) $idExistente === (string) $idNuevo;
        }

        $comienzaExistente = $this->normalizarFechaPadron($existente->comienza ?? null);
        $comienzaNuevo = $this->normalizarFechaPadron($nuevo->comienza ?? null);

        return $comienzaExistente !== '' && $comienzaExistente === $comienzaNuevo;
    }

    private function actualizarRegistrosPorIdentidad($modelo, $registrosExistentes, string $clave, array $datos): void
    {
        foreach ($registrosExistentes[$clave] ?? [] as $registro) {
            $modelo::whereKey($registro->getKey())->update($datos);
        }
    }


    public function ObtenerPadronFiltrado($impuesto, $request)
    {
        //Log::info('request', $request->all());
        //dd('hola');
        //Parametros de busqueda
        $search = $request->input('search_all');
        $search_folio = $request->input('search_folio');
        $filtros = $request->input('filtros', []);

        // Si no hay filtros, por defecto mostrar solo activos
        if (empty($filtros)) {
            $filtros[] = 'ACTIVO';
        }

        // Separar filtros por tipo
        $estados = array_intersect($filtros, ['ACTIVO', 'INACTIVO', 'PENDIENTE', 'MODIFICADO']);
        $administraciones = array_intersect($filtros, ['L', 'P', 'I']);
        $seguirPagando = array_intersect($filtros, ['SEGUIR-PAGANDO']);

        // Obtener todos los registros del padrón desde el servicio
        if ($impuesto === 'agua') {
            $padron = (new PadronAguaService())->obtenerPadronExistente();
        }
        if ($impuesto === 'tgi') {
            $padron = (new PadronTgiService())->obtenerPadronExistente();
        }

        if ($impuesto === 'gas') {
            $padron = (new PadronGasService())->obtenerPadronExistente();
        }

        if ($impuesto === 'api') {
            $padron = (new PadronApiService())->obtenerPadronExistente();
        }



        //filtrar por folio, pero el numero exacto, no si solo lo contiene
        if (!empty($search_folio)) {
            $padron = $padron->filter(function ($item) use ($search_folio) {
                // Comparación exacta
                return strtolower($item->folio) === strtolower($search_folio);
            });
        }


        // Filtrar por búsqueda
        if (!empty($search)) {
            $padron = $padron->filter(function ($item) use ($search) {
                return  str_contains(strtolower($item->calle), strtolower($search)) ||
                    str_contains(strtolower($item->partida), strtolower($search)) ||
                    str_contains(strtolower($item->clave), strtolower($search));
            });
        }

        // Aplicar filtros combinados
        $padron = $padron->filter(function ($item) use ($estados, $administraciones) {
            $estadoOk = empty($estados) || in_array(strtoupper($item->estado), $estados);
            $adminOk = empty($administraciones) || in_array(strtoupper($item->administra), $administraciones);
            return $estadoOk && $adminOk;
        });


        /* Log::info('padron', $padron->all());
        Log::info('seguirPagando', $seguirPagando); */
        if (!empty($seguirPagando)) {
            $padron = $padron->filter(function ($item) {
                return $item->seguir_pagando === 'S';
            });
        }


        //Log::info('padron', $padron->all());

        // Si hay parámetros de búsqueda, devolver JSON (para AJAX)
        if (!empty($search) || !empty($search_folio) || !empty($request->input('filtros'))) {
            return response()->json(['message' => 'Filtro actualizado correctamente.', 'data' => $padron->values()->all()]);
        }

        // Si no hay parámetros, devolver la vista completa
        return response()->json(['data' => $padron->values()->all()]);
    }

    public function actualizarPadronConcreto($request)
    {

        //Log::info('llego a actualizarPadron', $request->all());

        $modelo = $this->obtenerModeloPorImpuesto($request->impuesto);

        // Verificar si ya existe otro registro con el mismo folio, clave y partida

        /* $existe = $modelo::where('folio', $request->folio)
            ->where('clave', $request->clave)
            ->where('partida', $request->partida)
            ->where('id', '!=', $request->id)   // excluir el actual
            ->exists(); */
        //Log::info($modelo);
        //dd('hola');

        /* if ($existe) {
            return response()->json(['message' => 'Ya existe otro registro con el mismo folio, clave y partida.'], 400);
        }
 */
        // Si no existe duplicado, actualizar
        $registro = $modelo::findOrFail($request->id);
        $registro->folio = $request->folio;
        $registro->calle = $request->calle;
        $registro->partida = $request->partida;
        $registro->clave = $request->clave;
        $registro->estado = $request->estado;
        $registro->administra = $request->administra;
        $registro->seguir_pagando = $request->seguir_pagando;
        $registro->save();


        return response()->json(['message' => 'Registro actualizado correctamente.'], 200);
    }


    public function obtenerModeloPorImpuesto($impuesto)
    {
        $map = [
            'tgi'  => Tgi_padron::class,
            'agua' => Agua_padron::class,
            'gas'  => Gas_padron::class,
            'api'  => Api_padron::class,
        ];

        if (!isset($map[$impuesto])) {
            throw new \Exception("Impuesto no válido");
        }

        return $map[$impuesto];
    }
}
