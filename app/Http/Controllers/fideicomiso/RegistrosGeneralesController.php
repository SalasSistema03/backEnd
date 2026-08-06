<?php

namespace App\Http\Controllers\fideicomiso;

use App\Http\Controllers\Controller;
use App\Services\fideicomiso\RegistrosGeneralesService;
use Illuminate\Http\Request;

class RegistrosGeneralesController extends Controller 
{
    public function __construct(
        protected RegistrosGeneralesService $registrosGeneralesService
    ) {}

    // GET
    public function getDatosGeneralesController()
    {
        try {
            $registros = $this->registrosGeneralesService->getRegistrosGeneralesService();
            return response()->json([
                'status' => 'success',
                'data' => $registros
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los datos generales',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // GET
    public function getRegistroGeneralPorIdController($id)
    {
        try {
            $registro = $this->registrosGeneralesService->getRegistroGeneralPorIdService($id);
            return response()->json([
                'status' => 'success',
                'data' => $registro
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el registro general',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // POST
    public function postRegistroGeneralController(Request $request)
    {
        $request->validate([
            'periodo' => 'required',
        ]);

        try {
            $resultado = $this->registrosGeneralesService->postRegistroGeneralService($request);
            return response()->json([
                'status' => 'success',
                'message' => 'El registro se ha guardado correctamente',
                'data' => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar el registro general',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // PUT
    public function modificarRegistroGeneralController(Request $request, $id)
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
            'vencimiento' => 'sometimes|nullable|date',
        ]);

        try {
            $resultado = $this->registrosGeneralesService->modificarRegistroGeneralService($id, $datosValidados);
            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'El registro general se ha modificado correctamente'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al modificar el registro general',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }

    // DELETE
    public function eliminarRegistroGeneralController($id)
    {
        try {
            $resultado = $this->registrosGeneralesService->eliminarRegistroGeneralService($id);
            if ($resultado) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'El registro general se ha eliminado correctamente'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el registro general',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }


    public function cargaMasivaController(Request $request)
    {
        $request->validate([
            'periodo' => 'required|string',
            'servicio' => 'required|string',
            'monto_total' => 'required|numeric'
        ]);

        try {
            $this->registrosGeneralesService->procesarCargaMasiva(
                $request->periodo,
                $request->servicio,
                $request->monto_total
            );

            return response()->json([
                'status' => 'success',
                'message' => 'La carga masiva se ha procesado y distribuido correctamente.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la carga masiva',
                'ErrorBase' => $e->getMessage(),
            ], 500);
        }
    }
    
}