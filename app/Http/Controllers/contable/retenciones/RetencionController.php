<?php

namespace App\Http\Controllers\contable\retenciones;


use App\Http\Controllers\Controller;
use App\Models\Contable\retenciones\Padron_retenciones;
use App\Services\At_cl\ProvinciaService;
use App\Services\contable\retenciones\RetencionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class RetencionController extends Controller
{

    public function __construct(
        protected RetencionService $retencionService,
        protected ProvinciaService $provinciaService
    ) {}

    // GET
    public function getPadronRetencionCUILController($cuil)
    {
        try {
            $padronRetenciones = $this->retencionService->getPadronRetencionCUILService($cuil);
            return response()->json([
                'status' => 'success',
                'data' => $padronRetenciones
            ], 200);
        } catch (\Exception $e) {
            // Si algo falla (conexión, base de datos, etc.), esto te dirá QUÉ es en Postman
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el padrón de retenciones',
                'ErrorBase' => $e->getMessage(), // Esto te dirá el error real de la DB
            ], 500);
        }
    }

    // GET
    public function getBasePorcentualController()
    {
        try {
            $basePorcentuales = $this->retencionService->getBasePorcentual();
            return response()->json([
                'status' => 'success',
                'data' => $basePorcentuales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la base de retenciones',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // GET
    public function getVerficarComprobanteController(Request $request)
    {
        try {
            // Validar que lleguen los datos (Opcional pero recomendado)
            if (!$request->cuit || !$request->fecha) {
                return response()->json(['status' => 'error', 'message' => 'Faltan datos'], 400);
            }

            $existeComprobante = $this->retencionService->getVerificarComprobanteService($request->cuit, $request->fecha);

            return response()->json([
                'status' => 'success',
                'data'   => $existeComprobante // Aquí irá el objeto o null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al verificar el comprobante',
                'debug' => $e->getMessage(), // Solo para desarrollo
            ], 500);
        }
    }

    // GET
    public function getCalculoRetencion(Request $request)
    {
        try {
            // Validación básica
            $request->validate([
                'monto' => 'required|numeric',
                'cuit'  => 'required',
                'fecha' => 'required|date'
            ]);

            $resultado = $this->retencionService->getCalculoRetencionService(
                $request->monto,
                $request->cuit,
                $request->fecha
            );

            return response()->json([
                'status' => 'success',
                'data'   => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se pudo calcular la retención.',
                'debug'   => $e->getMessage()
            ], 500);
        }
    }

    // GET
    public function getTablaRetencionesController()
    {
        try {
            $tablaRetenciones = $this->retencionService->getTablaRetencionesService();
            return response()->json([
                'exito' => true,
                'totalDeRegistros' => $tablaRetenciones->count(),
                'comprobantes' => $tablaRetenciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error al obtener los registros'
            ], 500);
        }
    }

    // GET
    public function getProvinciasController()
    {
        try {
            $provincias = $this->provinciaService->getProvincias();
            return response()->json([
                'status' => 'success',
                'data' => $provincias
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las provincias',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    //GET
    public function getRetencionPorCUITController(String $cuit)
    {
        try {
            $retencion = $this->retencionService->getRetencionPorCUITService($cuit);
            return response()->json([
                'status' => 'success',
                'data' => $retencion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la retencion por cuit',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT
    public function modificarBasePorcentualController(Request $request)
    {
        $request->validate([
            'porcentual_dato' => 'required|numeric',
            'base_dato' => 'required|numeric'
        ]);

        try {
            $resultado = $this->retencionService->modificarBasePorcentualService($request);
            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'La base de retenciones se ha modificado correctamente'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al modificar la base de retenciones',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    public function postComprobanteController(Request $request)
    {
        try {
            $resultado = $this->retencionService->postComprobanteService($request);
            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'El registro de retención se ha guardado correctamente'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar el registro de retención',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // POST
    public function postPersonaRetencionController(Request $request)
    {
        $validated = $request->validate([
            'cuit_retencion' => 'required|string',
            'razon_social_retencion' => 'required|string',
            'domicilio_retencion' => 'required|string',
            'localidad_retencion' => 'required|string',
            'id_provincia_retencion' => 'required|numeric',
            'codigo_postal_retencion' => 'required|numeric'
        ]);

        try {
            // Se intancia el modelo cin los datos validados
            $persona = new Padron_retenciones($validated);
            $resultado = $this->retencionService->postPersonaRetencionService($persona);
            return response()->json([
                'status' => 'success',
                'message' => 'Persona guardada correctamente',
                'data' => $resultado
            ], 200);
        } catch (\Exception $e) {
            Log::info('error persona', [
                'exception' => $e
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar la persona',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }



    public function modgiciarRegistroRetencionController(Request $request, $id)
    {
        // 1. Validamos y guardamos solo los datos permitidos
        $datosValidados = $request->validate([
            'fecha_comprobante'   => 'sometimes|date',
            'calcula_base'        => 'sometimes|string|max:1',
            'numero_comprobante'  => 'sometimes|integer',
            'importe_comprobante' => 'sometimes|numeric',
            'importe_retencion'   => 'sometimes|numeric',
        ]);

        try {
            // 2. Pasamos el $id de la ruta y SOLO los datos validados
            $resultado = $this->retencionService->modgiciarRegistroRetencionService($id, $datosValidados);

            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'El registro de retención se ha modificado correctamente'
                ], 200);
            }

            // Caso en que el servicio no encuentra el registro
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el registro para modificar'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al modificar el registro de retención',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerSumasMensualesController(Request $request)
    {


        try {
            // Usamos el servicio inyectado
            $resultado = $this->retencionService->obtenerSumasMensualesService(
                $request->anio,
                $request->mes
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'suma_primera' => (float) ($resultado->suma_primera ?? 0),
                    'suma_segunda' => (float) ($resultado->suma_segunda ?? 0)
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las sumas mensuales',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }


    public function exportarRetencionesTXTController(RetencionService $retencionService)
    {
        $contenido = $retencionService->generarContenidoTxtService();

        if (empty($contenido)) {
            return response()->json(['message' => 'No hay registros para exportar'], 404);
        }

        $nombreArchivo = 'retenciones_' . date('Y-m-d_His') . '.txt';

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, $nombreArchivo, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function exportarRetencionesFaltantesTXTController(Request $request)
    {
        $registros = $request->input('registros', []);


        $contenido = "Razon Social\tCUIT\tFecha\tImporte Comprobante\tImporte Retención\n";

        foreach ($registros as $r) {
            $fila = [
                $r['razon_social_retencion'] ?? '',
                $r['cuit_retencion'] ?? '',
                $r['fecha_comprobante'] ?? '',
                $r['importe_comprobante'] ?? '',
                $r['importe_retencion'] ?? '',
            ];

            $contenido .= implode("\t", $fila) . "\n";
        }

        $cuit = $registros[0]['cuit_retencion'] ?? 'desconocido';
        $nombreArchivo = "retenciones_cuit_{$cuit}.txt";

        return Response::make($contenido, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename={$nombreArchivo}",
        ]);
    }
}
