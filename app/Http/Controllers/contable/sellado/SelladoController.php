<?php

namespace App\Http\Controllers\contable\sellado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\contable\sellado\DatosCalculoService;
use App\Services\contable\sellado\RegistroSelladoService;
use App\Services\contable\sellado\ValorDatosRegistralesService;
use App\Services\contable\sellado\PermitirAccesoSelladoService;

class SelladoController extends Controller
{
    // Definimos las propiedades para que estén disponibles en toda la clase
    protected $valorGastoAdminitrativo_service;
    protected $valorHoja_service;
    protected $valorSellado_service;

    public function __construct(
        protected RegistroSelladoService $registro_sellado,
        protected DatosCalculoService   $datosCalculoService,
    ) {
    }

    public function getDatosSelladoController()
    {
        try {
            // 1. Obtener el ID del usuario autenticado vía JWT/Token
            $usuario_id = auth('api')->id();
            // 2. Instanciar el servicio de permisos localmente
            $accessService = new PermitirAccesoSelladoService($usuario_id);
            // 3. Recopilación de Permisos de Botones
            $botones = [
                'datosDeCalculo' => $accessService->tieneAcceso('datosDeCalculo'),
                'acciones'       => $accessService->tieneAcceso('acciones'),
                'guardar'        => $accessService->tieneAcceso('guardar')
            ];
            $resultado = $this->registro_sellado->getRegistroSellado();
            return response()->json([
                'status' => 'success',
                'data' => $resultado,
                'permisos' => $botones
            ], 200);
        } catch (\Exception $e) {
            // Si algo falla (conexión, base de datos, etc.), esto te dirá QUÉ es en Postman
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los datos de sellado',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    //Esta funcion guarda/modifica el valor de "valor_registro_extra" en la tabla
    public function guardarValorRegistroExtraController(Request $request)
    {
        try {
            $this->datosCalculoService->setValorRegistroExtra($request->all()['valor_extra']);
            $this->datosCalculoService->setValoresRegistrales($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Valor de "valor_registro_extra" guardado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar el valor de "valor_registro_extra"',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    //Este metodo guarda/modifica los registros de la tabla "valor_gasto_administrativo"
    public function guardarValorGastoAdministrativoController(Request $request)
    {
        try {
            $this->datosCalculoService->setValoresGastoAdministrativo($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Valores de "valor_gasto_administrativo" guardado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar los valores de "valor_gasto_administrativo"',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    //Este metodo guarda/modifica los registros de la tabla "valor_hoja"
    public function guardarValorHojaController(Request $request)
    {
        try {
            //Llama al servicio datosCalculoService para guardar los valores de "valor_hoja"
            $this->datosCalculoService->setValoresHoja($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Valores de "valor_hoja" guardado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar los valores de "valor_hoja"',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    //Guarda los valores de sellado - DE DATOS DE CALCULO
    public function guardarValorSelladoController(Request $request)
    {
        try {
            $this->datosCalculoService->setValoresSellado($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Valores de "valor_sellado" guardado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al guardar los valores de "valor_sellado"',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    

    //Este metodo elimina todos los registros de la tabla "registro_sellado"
    public function eliminarRegistroSelladoController()
    {
        try {
            $this->registro_sellado->eliminarRegistro();

            return response()->json([
                'status' => 'success',
                'message' => 'Registros eliminados correctamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar registros: ' . $e->getMessage()
            ], 500);
        }
    }

    //Este metodo calcula el registro sellado (solo es el calculo, no guarda en la base de datos)
    public function calcularSelladoController(Request $request)
    {
        try {
            // 2. Llamar al servicio
            $resultado = $this->registro_sellado->calcularSellado($request->all());

            // 3. Retornar respuesta JSON para Vue.js
            return response()->json([
                'status' => 'success',
                'data'   => $resultado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar el cálculo: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. Para cuando el usuario hace clic en "Confirmar y Guardar"
    public function guardarSelladoController(Request $request)
    {
        try {
            $registro = $this->registro_sellado->guardarSellado($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Registro guardado correctamente',
                'data' => $registro
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }

    
    /* Lista los DATOS DE CALCULO que utiliza el sellado (Los datos que figuran en el engranaje)*/
    public function getDatosCalculo()
    {
        try {
            return response()->json( [
                'configuracion' => [
                    'valores_datos_registrales' => $this->datosCalculoService->getAllValorDatosRegistrales(),
                    'valores_gasto_administrativo' => $this->datosCalculoService->getAllValorGastoAdministrativo(),
                    'valores_hoja' => $this->datosCalculoService->getAllValorHoja(),
                    'valores_sellado' => $this->datosCalculoService->getAllValorSellado(),
                    'valor_registro_extra' => $this->datosCalculoService->getValorRegistroExtra(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al obtener los datos de calculo: '], 500);
        }
    }

}
