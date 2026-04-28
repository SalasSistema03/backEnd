<?php

namespace App\Services\impuesto\IMPUESTO;


use App\Models\impuesto\Tgi_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\impuesto\AGUA\PadronAguaService;
use App\Services\impuesto\TGI\PadronTgiService;
use App\Models\impuesto\Agua_padron;
use App\Models\impuesto\Gas_padron;
use App\Services\impuesto\GAS\PadronGasService;
use Carbon\Carbon;

class PadronImpuestoService
{

    //Esta funcion llama a la funcion obtenerPadronAGUA para obtener el padrón

    public function actualizarPadronImpuesto($impuesto)
    {

        if ($impuesto === 'agua' || $impuesto === 'tgi' || $impuesto === 'gas') {
            $this->actualizarPadron($impuesto);
        }
    }

    public function actualizarPadron($impuesto)
    {
        if ($impuesto === 'tgi' || $impuesto === 'agua') {
            $this->obtenerPadron($impuesto);
            return response()->json(['message' => 'Padrón ' . strtoupper($impuesto) . ' actualizado correctamente.']);
        }
        if ($impuesto === 'gas') {
            $this->obtenerPadronServicio($impuesto);
            return response()->json(['message' => 'Padrón ' . strtoupper($impuesto) . ' actualizado correctamente.']);
        }
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

        $existente = collect($padronExistente)->mapWithKeys(
            fn($item) => [$item->folio . '-' . $item->partida => $item]
        );

        $nuevo = collect($nuevoPadron)->mapWithKeys(
            fn($item) => [$item->folio . '-' . $item->partida => $item]
        );

        $hoy = now();

        $nuevosRegistros    = collect();
        $actualizadosEstado = collect();

        foreach ($nuevo as $key => $registro) {

            $estadoCalculado   = $this->calcularEstadoGas($registro->rescicion, $hoy);
            $registroExistente = $existente[$key] ?? null;

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
                if ($registroExistente->estado !== $estadoCalculado) {
                    $actualizadosEstado->push([
                        'folio'           => $registro->folio,
                        'partida'         => $registro->partida,
                        'estado_anterior' => $registroExistente->estado,
                        'estado_nuevo'    => $estadoCalculado,
                        'rescicion'       => $registro->rescicion,
                    ]);
                }

                Gas_padron::where('folio',   $registro->folio)
                    ->where('partida', $registro->partida)
                    ->update($data);
            } else {
                $data['folio']   = is_numeric($registro->folio) ? $registro->folio : null;
                $data['partida'] = $registro->partida ?? '';

                Gas_padron::create($data);
                $nuevosRegistros->push($registro);
            }
        }

        return [
            'nuevos'              => $nuevosRegistros->values(),
            'actualizados_estado' => $actualizadosEstado->values(),
        ];
    }

    public function obtenerPadron($impuesto)
    {
        if ($impuesto === 'tgi') {
            $padronExistente = Tgi_padron::all();
            $nuevoPadron = (new PadronTgiService)->consultaObtenerPadronTGI();
        }

        if ($impuesto === 'agua') {
            $padronExistente = Agua_padron::all();
            $nuevoPadron = (new PadronAguaService)->consultaObtenerPadronAgua();
        }

        // Claves únicas
        $existente = collect($padronExistente)->mapWithKeys(function ($item) {
            return [$item->folio . '-' . $item->partida => $item];
        });

        $nuevo = collect($nuevoPadron)->mapWithKeys(function ($item) {
            return [$item->folio . '-' . $item->partida => $item];
        });

        // 1. Nuevos registros
        $nuevosRegistros = $nuevo->diffKeys($existente);

        // 0. Reactivar INACTIVOS
        $reactivar = $nuevo->filter(function ($registro, $key) use ($existente) {
            return isset($existente[$key]) && $existente[$key]->estado === 'INACTIVO';
        });

        foreach ($reactivar as $registro) {
            $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;

            $modelo::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update([
                    'estado' => 'ACTIVO',
                    'calle' => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                    'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
        }

        // 2. Sync total
        foreach ($nuevo as $key => $registro) {
            $registroExistente = $existente[$key] ?? null;
            $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;

            if ($registroExistente) {
                $modelo::where('folio', $registro->folio)
                    ->where('partida', $registro->partida)
                    ->update([
                        'estado' => 'ACTIVO',
                        'calle' => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                        'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                        'abona' => $registro->abona ?? '',
                        'administra' => $registro->administra ?? '',
                        'empresa' => $registro->empresa ?? 0,
                        'comienza' => $registro->comienza ?? 0,
                        'rescicion' => $registro->rescicion ?? 0,
                    ]);
            } else {
                $modelo::create([
                    'folio' => is_numeric($registro->folio) ? $registro->folio : null,
                    'calle' => !empty($registro->calle) ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8') : '',
                    'partida' => $registro->partida ?? '',
                    'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'estado' => 'ACTIVO',
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
            }
        }

        // 3. Inactivar / Pendiente
        $posiblesInactivos = $existente->diffKeys($nuevo);

        $registrosInactivos = $posiblesInactivos->map(function ($registro) use ($impuesto) {

            if (empty($registro->rescicion)) {
                $registro->nuevo_estado = 'INACTIVO';
                return $registro;
            }

            $fechaRescision = \Carbon\Carbon::parse($registro->rescicion);
            $fechaActual = \Carbon\Carbon::now();

            // 🔹 TGI (igual que antes)
            if ($impuesto === 'tgi') {
                $mesActual = ($fechaActual->year * 12) + $fechaActual->month;
                $mesRescision = ($fechaRescision->year * 12) + $fechaRescision->month;

                $diferenciaMeses = $mesActual - $mesRescision;

                $registro->nuevo_estado = $diferenciaMeses >= 1 ? 'INACTIVO' : 'ACTIVO';
                return $registro;
            }

            // 🔹 AGUA (mes calendario)
            if ($impuesto === 'agua') {

                $mesActual = ($fechaActual->year * 12) + $fechaActual->month;
                $mesRescision = ($fechaRescision->year * 12) + $fechaRescision->month;

                $diferenciaMeses = $mesActual - $mesRescision;

                if ($diferenciaMeses >= 3) {
                    $registro->nuevo_estado = 'INACTIVO';
                } else {
                    $registro->nuevo_estado = 'PENDIENTE';
                }

                return $registro;
            }

            return $registro;
        });

        // Update final
        foreach ($registrosInactivos as $registro) {
            $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;

            $modelo::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update(['estado' => $registro->nuevo_estado]);
        }

        return [
            'nuevos' => $nuevosRegistros->values(),
            'inactivos' => $registrosInactivos->values(),
        ];
    }

    public function ObtenerPadronFiltrado($impuesto, $request)
    {

        //Parametros de busqueda
        $search = $request->input('search_all');
        $search_folio = $request->input('search_folio');
        $filtros = $request->input('filtros', []);

        // Si no hay filtros, por defecto mostrar solo activos
        if (empty($filtros)) {
            $filtros[] = 'ACTIVO';
        }

        // Separar filtros por tipo
        $estados = array_intersect($filtros, ['ACTIVO', 'INACTIVO', 'PENDIENTE']);
        $administraciones = array_intersect($filtros, ['L', 'P', 'I']);

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

        // Si hay parámetros de búsqueda, devolver JSON (para AJAX)
        if (!empty($search) || !empty($search_folio) || !empty($request->input('filtros'))) {
            return response()->json(['message' => 'Filtro actualizado correctamente.', 'data' => $padron->values()->all()]);
        }

        // Si no hay parámetros, devolver la vista completa
        return response()->json(['data' => $padron->values()->all()]);
    }

    public function actualizarPadronConcreto($request)
    {
        $modelo = $this->obtenerModeloPorImpuesto($request->impuesto);
        // Verificar si ya existe otro registro con el mismo folio, clave y partida

        $existe = $modelo::where('folio', $request->folio)
            ->where('clave', $request->clave)
            ->where('partida', $request->partida)
            ->where('id', '!=', $request->id) // excluir el actual
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya existe otro registro con el mismo folio, clave y partida.'], 400);
        }

        // Si no existe duplicado, actualizar
        $registro = $modelo::findOrFail($request->id);
        $registro->folio = $request->folio;
        $registro->calle = $request->calle;
        $registro->partida = $request->partida;
        $registro->clave = $request->clave;
        $registro->estado = $request->estado;
        $registro->administra = $request->administra;
        $registro->save();


        return response()->json(['message' => 'Registro actualizado correctamente.'], 200);
    }


    public function obtenerModeloPorImpuesto($impuesto)
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
}
