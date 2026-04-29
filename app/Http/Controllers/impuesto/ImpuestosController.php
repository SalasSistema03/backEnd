<?php

namespace App\Http\Controllers\impuesto;


use App\Http\Controllers\Controller;
use App\Services\impuesto\TGI\CargaTgiService;
use App\Services\impuesto\TGI\PadronTgiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use App\Services\impuesto\AGUA\PadronAguaService;
use App\Services\impuesto\AGUA\CargaAguaService;
use App\Services\impuesto\GAS\CargaGasService;
use App\Services\impuesto\Impuesto\PadronImpuestoService;
use App\Services\impuesto\Impuesto\CargaImpuestoService;


class ImpuestosController extends Controller
{



    public function actualizarPadron($impuesto)
    {
        if ($impuesto === 'tgi' || $impuesto === 'agua' || $impuesto === 'gas') {
            return (new PadronImpuestoService())->actualizarPadronImpuesto($impuesto);
        }
    }

    public function filtradoPadron(Request $request, $impuesto)
    {
        if ($impuesto === 'tgi' || $impuesto === 'agua' || $impuesto === 'gas') {
            return (new PadronImpuestoService())->ObtenerPadronFiltrado($impuesto, $request);
        }
    }

    public function actualizarImpuesto(Request $request)
    {
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            return (new PadronImpuestoService())->actualizarPadronConcreto($request);
        }
    }


    /* ===================================== ACA EMPIEZA CARGA =====================================*/

    public function padronCarga(Request $request)
    {
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            return app(CargaImpuestoService::class)->padronCarga($request);
        }
    }


    public function cargaManual(Request $request)
    {
        Log::info('cargaManual', $request->all());
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            return app(CargaImpuestoService::class)->obtenerRegistroPadronManual($request->folio, $request->empresa, $request->impuesto);
        }
    }


    public function cargaNuevoManual(Request $request)
    {
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            return app(CargaImpuestoService::class)->cargarNuevoImpuestoManual($request);
        }
    }

    public function cargarNuevoImpuesto(Request $request)
    {
        if ($request->impuesto === 'tgi') {


            $codigoBarras = $request->codigo_barras;

            if (!$codigoBarras) {
                Log::error('El campo código de barras es obligatorio');
                return redirect()->back()->with('error', 'El campo código de barras es obligatorio.');
            }
            if (empty($codigoBarras) || strlen($codigoBarras) !== 35) {
                Log::error('Debés ingresar un código de barras válido de 35 caracteres');
                return redirect()->back()->with('error', 'Debés ingresar un código de barras válido de 35 caracteres.');
            }

            return app(CargaTgiService::class)->cargarNuevoTgiService($codigoBarras);
        }
        if ($request->impuesto === 'agua') {
            $codigoBarras = $request->codigo_barras;

            if (!$codigoBarras) {
                return response()->json(['error' => 'El campo código de barras es obligatorio'], 400);
            }

            if (empty($codigoBarras) || strlen($codigoBarras) !== 41) {
                return response()->json(['error' => 'Debés ingresar un código de barras válido de 35 caracteres'], 400);
            }

            return app(CargaAguaService::class)->cargarNuevoAguaService($codigoBarras);
        }

        if ($request->impuesto === 'gas') {
            Log::info('controlador impyuesyo');
            $codigoBarras = $request->codigo_barras;
            if (!$codigoBarras) {
                return response()->json(['error' => 'El campo código de barras es obligatorio'], 400);
            }
            if (empty($codigoBarras) || strlen($codigoBarras) !== 52) {
                Log::info($codigoBarras);
                Log::info('El código de barras no tiene 51 caracteres');
                return response()->json(['error' => 'Debés ingresar un código de barras válido de 51 caracteres'], 400);
            }
            return app(CargaGasService::class)->cargarNuevoGasService($codigoBarras);
        }
    }

    public function exportarFaltantes(Request $request)
    {
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            $registros = app(CargaImpuestoService::class)->exportarFaltantesService($request->anio, $request->mes, $request->impuesto);

            $contenido = '';

            foreach ($registros as $r) {
                $fila = [
                    $r->folio,
                    $r->partida,
                    $r->clave,
                    $r->abona,
                    $r->administra,
                    $r->empresa,
                    $r->estado,
                    Carbon::parse($r->comienza)->format('Y-m-d'),
                    Carbon::parse($r->rescicion)->format('Y-m-d'),
                ];

                $contenido .= implode("\t", $fila) . "\n";
            }

            $nombreArchivo = "agua_faltantes_{$request->anio}_{$request->mes}.txt";

            return Response::make($contenido, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => "attachment; filename={$nombreArchivo}",
            ]);
        }
    }


    public function sumarMontos(Request $request)
    {
        //Log::info('llego al controller', [$request->all()]);
        if ($request->impuesto === 'gas') {
            $total = app(CargaImpuestoService::class)->sumarMontosGasService($request->anio, $request->mes,  $request->dia);
            return response()->json([
                'total' => $total,
            ]);
        }

        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua') {
            $total = app(CargaImpuestoService::class)->sumarMontosService($request->anio, $request->mes, $request->impuesto);
            $totalSalas = app(CargaImpuestoService::class)->sumarMontosSalasService($request->anio, $request->mes, $request->impuesto);


            //Log::info('esta es la respuesta ', [$total, $totalSalas]);
            return response()->json([
                'total' => $total,
                'totalSalas' => $totalSalas,
            ]);
        }
    }

    public function MostrarBroche(Request $request)
    {
        if ($request->impuesto === 'gas') {
            return app(CargaImpuestoService::class)->generarDistribucionGasBroches($request->anio, $request->mes, $request->dia, $request->cant_broches);
        }
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            return app(CargaImpuestoService::class)->generarDistribucionBroches($request->anio, $request->mes, $request->cant_broches, $request->impuesto);
        }
    }

    public function guardarBroches(Request $request)
    {
        if ($request->impuesto === 'gas') {
            $resultado = app(CargaImpuestoService::class)->generarDistribucionGasBroches($request->anio, $request->mes, $request->dia, $request->cant_broches);
            app(CargaImpuestoService::class)->guardarDistribucionBroches($resultado['registrosFiltrados'], $request->impuesto);
            return response()->json([
                'status' => 'success',
                'message' => 'Los broches se guardaron correctamente.'
            ]);
        }
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {


            $resultado = app(CargaImpuestoService::class)->generarDistribucionBroches($request->anio, $request->mes, $request->cant_broches, $request->impuesto);

            app(CargaImpuestoService::class)->guardarDistribucionBroches($resultado['registrosFiltrados'], $request->impuesto);

            return response()->json([
                'status' => 'success',
                'message' => 'Los broches se guardaron correctamente.'
            ]);
        }
    }

    public function guardarBrocheSALAS(Request $request)
    {
        Log::info('guardo el broche salas?', [$request->all()]);
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua') {

            try {
                app(CargaImpuestoService::class)->guardarDistribucionBrocheSALAS($request->anio, $request->mes, $request->impuesto);


                return response()->json(['status' => 'success', 'message' => 'Los broches se guardaron correctamente.']);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hubo un problema al guardar los broches: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    public function modificarBajadoController(Request $request)
    {
        if ($request->impuesto === 'tgi' || $request->impuesto === 'agua' || $request->impuesto === 'gas') {
            app(CargaImpuestoService::class)->modificarBajadoService($request->anio, $request->mes, $request->impuesto);

            return response()->json(['status' => 'success', 'message' => 'El bajado se modificó correctamente.']);
        }
    }

    public function modificarEstadoTGIController(Request $request)
    {
        $data = $request->data ?? $request->all()[0]['data'] ?? null;

        if ($data['impuesto'] === 'tgi' || $data['impuesto'] === 'agua' || $data['impuesto'] === 'gas') {

            $estado = $data['estado'];
            $id = $data['padron'] ?? $data['id'] ?? null;

            app(CargaImpuestoService::class)->modificarEstado($id, $estado, $data['impuesto']);

            return response()->json(['status' => 'success', 'message' => 'El estado se modificó correctamente.']);
        }
    }

    public function eliminarRegistro(Request $request)
    {
        $id = $request->id;

        app(CargaImpuestoService::class)->eliminarRegistro($id, $request->impuesto);

        return response()->json(['status' => 'success', 'message' => 'El registro se ha eliminado correctamente.']);
    }
}
