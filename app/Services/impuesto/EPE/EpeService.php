<?php

namespace App\Services\impuesto\EPE;

use App\Models\At_cl\Empresas_propiedades;
use App\Models\impuesto\Epe;
use App\Models\impuesto\Gas_padron;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\impuesto\Gas_carga;
use App\Services\impuesto\GAS\ExtraerCodBarraGas;
use Carbon\Carbon;
use App\Services\impuesto\IMPUESTO\CargaImpuestoService;

class EpeService
{
    public function buscarEpe($data)
    {
        if (!empty($data['folio']) && !empty($data['empresa'])) {
            // Caso 1: Búsqueda por folio y empresa
            $folio = $data['folio'];
            $empresa = $data['empresa'];

            // 1. Buscamos el propiedad_id correspondiente a ese folio y empresa
            $relacion = Empresas_propiedades::where('folio', $folio)
                ->where('empresa_id', $empresa)
                ->first();
            if (!$relacion) {
                return [];
            }
            // 2. Traemos los registros de Epe con la misma estructura que por cliente
            $resultado = Epe::where('propiedad_id', $relacion->propiedad_id)
                ->with([
                    'propiedad:id',
                    'propiedad.empresas:id'
                ])
                ->get();


            // Log::info('resultado', [$resultado]);
            return $resultado;

            // Tu lógica para folio y empresa aquí...

        } elseif (!empty($data['numero_cliente'])) {
            // Caso 2: Búsqueda por número de cliente
            $numeroCliente = $data['numero_cliente'];

            $resultado = Epe::where('cliente', $numeroCliente)
                ->with([
                    'propiedad:id',
                    'propiedad.empresas:id'
                ])
                ->get();

            return $resultado;
        } else {
            //En caso de que no me llegue nada devemos traer todo los datos
            $resultado = Epe::with([
                'propiedad:id',
                'propiedad.empresas:id'
            ])
                ->get();
            //Log::info('resultado', [$resultado]);
            return $resultado;
        }
    }

    public function cargarEpe($data)
    {
        // Si viene envuelto dentro de 'data', lo extraemos
        $params = isset($data['data']) ? $data['data'] : $data;
        $empresa = $params['empresa'] ?? null;
        $folioSalas = $params['folio_salas'] ?? null;

        // Validar que empresa y folio_salas no sean vacíos
        if (empty($empresa) || empty($folioSalas)) {
            return response()->json([
                'resultado' => 'Debe ingresar el folio salas y la empresa',
            ], 422);
        }

        $propiedad_id = Empresas_propiedades::where('folio', $folioSalas)
            ->where('empresa_id', $empresa)
            ->first();

        if ($propiedad_id) {
            Epe::create([
                'cliente' => $params['numero_cliente'] ?? null,
                'plan' => $params['plan'] ?? null,
                'ruta' => $params['ruta'] ?? null,
                'folio' => $params['folio'] ?? null,
                'ds' => $params['ds'] ?? null,
                'propiedad_id' => $propiedad_id->propiedad_id
            ]);
            return response()->json([
                'resultado' => 'Epe cargado correctamente',
            ]);
        } else {
            return response()->json([
                'resultado' => 'El folio salas ingresado es incorrecto o no pertenece a la empresa',
            ], 422);
        }
    }

    public function editarEpe($data)
    {
        // Si viene envuelto dentro de 'data', lo extraemos
        $params = isset($data['data']) ? $data['data'] : $data;
        $empresa = $params['empresa'] ?? null;
        $folioSalas = $params['folio_salas'] ?? null;

        // Validar que empresa y folio_salas no sean vacíos
        if (empty($empresa) || empty($folioSalas)) {
            return response()->json([
                'resultado' => 'Debe ingresar el folio salas y la empresa',
            ], 422);
        }

        // Validar que el registro a editar exista
        $epe = Epe::find($params['id'] ?? null);
        if (!$epe) {
            return response()->json([
                'resultado' => 'El registro EPE no existe',
            ], 422);
        }

        $propiedad_id = Empresas_propiedades::where('folio', $folioSalas)
            ->where('empresa_id', $empresa)
            ->first();

        if ($propiedad_id) {
            $epe->update([
                'cliente' => $params['numero_cliente'] ?? null,
                'plan' => $params['plan'] ?? null,
                'ruta' => $params['ruta'] ?? null,
                'folio' => $params['folio'] ?? null,
                'ds' => $params['ds'] ?? null,
                'propiedad_id' => $propiedad_id->propiedad_id
            ]);
            return response()->json([
                'resultado' => 'Epe actualizado correctamente',
            ]);
        } else {
            return response()->json([
                'resultado' => 'El folio salas ingresado es incorrecto o no pertenece a la empresa',
            ], 422);
        }
    }
}
