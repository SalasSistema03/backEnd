<?php

namespace App\Http\Controllers\fideicomiso;

use App\Http\Controllers\Controller;
use App\Services\fideicomiso\RegistrosMensualesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Spatie\SimpleExcel\SimpleExcelReader;



class RegistrosMensualesController extends Controller
{
    public function __construct(
        protected RegistrosMensualesService $registrosMensualesService
    ) {}

    // GET ALL
    public function getRegistrosMensualesController()
    {
        try {
            $registros = $this->registrosMensualesService->getRegistrosMensualesService();
            return response()->json([
                'status' => 'success',
                'data' => $registros
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los registros mensuales',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // GET BY ID
    public function getRegistroMensualPorIdController($id)
    {
        try {
            $registro = $this->registrosMensualesService->getRegistroMensualPorIdService($id);
            return response()->json([
                'status' => 'success',
                'data' => $registro
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el registro mensual',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // GET BY ID UNIDAD
    public function getRegistrosPorUnidadController($idUnidad)
    {
        try {
            $registros = $this->registrosMensualesService->getRegistrosPorUnidadService($idUnidad);
            return response()->json([
                'status' => 'success',
                'data' => $registros
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los registros mensuales de la unidad',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // POST
    public function postRegistroMensualController(Request $request)
    {
        $request->validate([
            'periodo' => 'required',
            'id_unidad' => 'required|integer',
        ]);

        try {
            $resultado = $this->registrosMensualesService->postRegistroMensualService($request);
            return response()->json([
                'status' => 'success',
                'message' => 'El registro mensual se ha guardado correctamente',
                'data' => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar el registro mensual',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT
    public function modificarRegistroMensualController(Request $request, $id)
    {
        $datosValidados = $request->validate([
            'tgi' => 'sometimes|nullable|numeric',
            'agua' => 'sometimes|nullable|numeric',
            'api' => 'sometimes|nullable|numeric',
            'luz' => 'sometimes|nullable|numeric',
            'seguro' => 'sometimes|nullable|numeric',
            'limpieza' => 'sometimes|nullable|numeric',
            'ascensor' => 'sometimes|nullable|numeric',
            'honorario' => 'sometimes|nullable|numeric',
            'periodo' => 'sometimes|string',
            'id_unidad' => 'sometimes|integer',
            'pagado' => 'sometimes|string|max:1', 
        ]);

        try {
            $resultado = $this->registrosMensualesService->modificarRegistroMensualService($id, $datosValidados);
            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'El registro mensual se ha modificado correctamente'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al modificar el registro mensual',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE
    public function eliminarRegistroMensualController($id)
    {
        try {
            $resultado = $this->registrosMensualesService->eliminarRegistroMensualService($id);
            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'El registro mensual se ha eliminado correctamente'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el registro mensual',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

public function comprobantesPdfController(Request $request)
    {
        Log::info('entro a comprobantesPdfController');
        $data = $request->all();

        $periodo = $data['registro']['periodo'] ?? null;
        
        if ($periodo) {
            // 1. Buscamos el 100% (Registro General)
            $registroGeneral = \App\Models\fideicomiso\RegistrosGenerales::where('periodo', $periodo)->first();
            $data['registro_general'] = $registroGeneral ? $registroGeneral->toArray() : [];

            // 2. Buscamos TODOS los registros de las unidades para armar la tabla gigante
            $todosLosRegistros = \App\Models\fideicomiso\RegistrosMensuales::where('periodo', $periodo)
                ->join('unidades', 'registros_mensuales.id_unidad', '=', 'unidades.id')
                ->select('registros_mensuales.*', 'unidades.piso', 'unidades.unidad', 'unidades.propietario', 'unidades.porcentual')
                ->orderBy('unidades.piso')
                ->orderBy('unidades.unidad')
                ->get();
                
            $data['todos_los_registros'] = $todosLosRegistros->toArray();
        } else {
            $data['registro_general'] = [];
            $data['todos_los_registros'] = [];
        }

        $html = view('pdfs.fideicomiso.liquidacionFideicomiso', compact('data'))->render();
        
        // A4 Apaisado es ideal para meter 11 columnas
        $orientacion = 'landscape'; 

        return response()->streamDownload(function () use ($html, $orientacion) {
            echo \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->margins(8, 8, 8, 8) // Achicamos un poco los márgenes para ganar espacio
                ->showBackground()
                ->emulateMedia('print')
                ->setOption('displayHeaderFooter', false) // Quitamos header/footer automático para aprovechar toda la hoja
                ->$orientacion()
                ->pdf();
        }, 'Detalle_de_Liquidacion.pdf');
    }
}
