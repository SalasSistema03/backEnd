<?php

namespace App\Services\impuesto\IMPUESTO;


use App\Models\impuesto\Tgi_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\impuesto\AGUA\PadronAguaService;
use App\Services\impuesto\TGI\PadronTgiService;
use App\Models\impuesto\Agua_padron;

class PadronImpuestoService
{

    //Esta funcion llama a la funcion obtenerPadronAGUA para obtener el padrón

    public function actualizarPadronImpuesto($impuesto)
    {

        if ($impuesto === 'agua' || $impuesto === 'tgi') {
            $this->actualizarPadron($impuesto);
        }
    }

    public function actualizarPadron($impuesto)
    {
        if ($impuesto === 'agua' || $impuesto === 'tgi') {
            $this->obtenerPadron($impuesto);
            return response()->json(['message' => 'Padrón ' . strtoupper($impuesto) . ' actualizado correctamente.']);
        }
    }

    public function obtenerPadron($impuesto)
    {

        if ($impuesto === 'agua') {
            $padronExistente = Agua_padron::all(); // registros actuales en la tabla
            $nuevoPadron = (new PadronAguaService)->consultaObtenerPadronAGUA(); // registros nuevos desde la consulta externa - sys

            // Limpiar partida: eliminar guiones y barras
            foreach ($nuevoPadron as $row) {
                if (isset($row->partida)) {
                    $row->partida = str_replace(['-', '/'], '', $row->partida);
                }
            }
        }

        if ($impuesto === 'tgi') {
            $padronExistente = Tgi_padron::all(); // registros actuales en la tabla
            $nuevoPadron = (new PadronTgiService)->consultaObtenerPadronTGI(); // registros nuevos desde la consulta externa
        }

        // Convertir a colecciones con claves únicas
        $existente = collect($padronExistente)->mapWithKeys(function ($item) {
            return [$item->folio . '-' . $item->partida => $item];
        });

        $nuevo = collect($nuevoPadron)->mapWithKeys(function ($item) {
            return [$item->folio . '-' . $item->partida => $item];
        });

        //  1. Detectar nuevos registros
        $nuevosRegistros = $nuevo->diffKeys($existente);


        //  0. Reactivar registros que existen pero están INACTIVOS
        // Filtra aquellos registros que, teniendo la misma clave en $existente, su estado sea 'INACTIVO'
        $reactivar = $nuevo->filter(function ($registro, $key) use ($existente) {
            return isset($existente[$key]) && $existente[$key]->estado === 'INACTIVO';
        });

        // Itera sobre los registros a reactivar y actualiza su estado a ACTIVO
        foreach ($reactivar as $registro) {
            $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;
            $modelo::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update([
                    'estado' => 'ACTIVO',
                    'calle' => !empty($registro->calle)
                        ? mb_convert_encoding($registro->calle, 'UTF-8', 'UTF-8')
                        : '',
                    'clave' => is_numeric($registro->clave) ? $registro->clave : null,
                    'abona' => $registro->abona ?? '',
                    'administra' => $registro->administra ?? '',
                    'empresa' => $registro->empresa ?? 0,
                    'comienza' => $registro->comienza ?? 0,
                    'rescicion' => $registro->rescicion ?? 0,
                ]);
        }

        //  2. Sincronizar todos los registros del nuevo padrón con la BD local
        // Itera sobre cada registro del nuevo padrón para insertarlo o actualizarlo
        foreach ($nuevo as $key => $registro) {
            // Verifica si el registro ya existe en la BD local usando la clave compuesta
            $registroExistente = $existente[$key] ?? null;
            if ($registroExistente) {
                // Si existe: actualiza todos sus campos (manteniéndolo ACTIVO)
                $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;
                $modelo::where('folio', $registro->folio)->where('partida', $registro->partida)->update([
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
                // Si no existe: crea un nuevo registro en la BD local
                $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;
                $modelo::create(
                    [
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
                    ]
                );
            }
        }


        //  3. Detectar registros que ya no están en el nuevo padrón y marcarlos como INACTIVO
        // Aquí se comparan las claves: todo lo que está en $existente pero no en $nuevo debe desactivarse
        $registrosInactivos = $existente->diffKeys($nuevo);

        // Itera sobre los registros que ya no aparecen en el sistema externo
        foreach ($registrosInactivos as $registro) {
            $modelo = $impuesto === 'agua' ? Agua_padron::class : Tgi_padron::class;
            $modelo::where('folio', $registro->folio)
                ->where('partida', $registro->partida)
                ->update(['estado' => 'INACTIVO']);
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
        $estados = array_intersect($filtros, ['ACTIVO', 'INACTIVO']);
        $administraciones = array_intersect($filtros, ['L', 'P', 'I']);

        // Obtener todos los registros del padrón desde el servicio
        if ($impuesto === 'agua') {
            $padron = (new PadronAguaService())->obtenerPadronExistente();
        }
        if ($impuesto === 'tgi') {
            $padron = (new PadronTgiService())->obtenerPadronExistente();
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
        // Verificar si ya existe otro registro con el mismo folio, clave y partida
        $modelo = $request->impuesto === 'tgi' ? Tgi_padron::class : Agua_padron::class;
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
}
