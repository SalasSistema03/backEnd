<?php

namespace App\Http\Controllers\contable\sellado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\contable\sellado\Registro_sellado;
use App\Models\contable\sellado\Valor_registro_extra;
use App\Services\contable\sellado\RegistroSelladoService;
use App\Services\contable\sellado\ValorDatosRegistralesService;
use App\Services\contable\sellado\ValorGastoAdminitrativoService;
use App\Services\contable\sellado\ValorHojaService;
use App\Services\contable\sellado\ValorSelladoService;
use App\Services\contable\sellado\PermitirAccesoSelladoService;

class SelladoController extends Controller
{
    // Definimos las propiedades para que estén disponibles en toda la clase
    protected $valorGastoAdminitrativo_service;
    protected $valorHoja_service;
    protected $valorSellado_service;
    protected $valorDatosRegistrales_service;

    public function __construct(
       protected RegistroSelladoService $registro_sellado,
        ValorGastoAdminitrativoService $valorGastoAdminitrativo,
        ValorHojaService $valorHoja,
        ValorSelladoService $valorSellado,
        ValorDatosRegistralesService $valorDatosRegistrales
    ) {
        $this->valorGastoAdminitrativo_service = $valorGastoAdminitrativo;
        $this->valorHoja_service = $valorHoja;
        $this->valorSellado_service = $valorSellado;
        $this->valorDatosRegistrales_service = $valorDatosRegistrales;
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


    public function getDatosSelladoController2()
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

            // 4. Obtención de Datos
            // CAMBIO: Usamos orderBy porque latest() busca 'created_at' y da error 500 si no existe.
            $valores = Registro_sellado::orderBy('id_registro_sellado', 'desc')->get();

            $valor_registro_extra = Valor_registro_extra::first()->valor_extra ?? 0;

            $otrosValores = [
                'valorGastoAdministrativo' => $this->valorGastoAdminitrativo_service->getAllValorGastoAdministrativo(),
                'valorHoja'                => $this->valorHoja_service->getAllValorHoja(),
                'valorSellado'             => $this->valorSellado_service->getAllValorSellado(),
                'valorDatosRegistrales'    => $this->valorDatosRegistrales_service->getAllValorDatosRegistrales()
            ];

            // 5. Respuesta JSON
            return response()->json([
                'status' => 'success',
                'data' => [
                    'registros' => $valores,
                    'configuracion' => [
                        'valor_extra' => $valor_registro_extra,
                        'otros' => $otrosValores
                    ]
                ],
                'permissions' => $botones
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


    public function getDatosSelladoController3()
    {
        try {
            // El servicio de acceso debería ser inyectado o resuelto por el contenedor, no con 'new'
            $usuario_id = auth('api')->id();
            $accessService = app(PermitirAccesoSelladoService::class, ['usuario_id' => $usuario_id]);

            // Centralizamos la lógica en un solo servicio de orquestación
            $data = $this->registro_sellado->getRegistroSellado();

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'permissions' => [
                    'datosDeCalculo' => $accessService->tieneAcceso('datosDeCalculo'),
                    'acciones'       => $accessService->tieneAcceso('acciones'),
                    'guardar'        => $accessService->tieneAcceso('guardar')
                ]
            ], 200);
        } catch (\Exception $e) {
            // Usa Log para guardar el error real y no lo expongas todo al cliente en producción


            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los datos',
                'debug' => config('app.debug') ? $e->getMessage() : null // Solo mostrar debug en desarrollo
            ], 500);
        }
    }


    public function guardarDatosCalculoController(Request $request)
    {
        $mensajes = [];

        try {
            // En Vue, enviarás un objeto: { check_registro: true, precio1: 100, ... }
            if ($request->boolean('check_registro')) { // boolean() es más limpio que == 1
                $valoresRegistrales = [
                    ['id_valor_datos_registrales' => 1, 'precio' => $request->input('precio1'), 'valor_limite' => $request->input('valor_limite1')],
                    ['id_valor_datos_registrales' => 2, 'precio' => $request->input('precio2'), 'valor_limite' => $request->input('valor_limite2')],
                    ['id_valor_datos_registrales' => 3, 'precio' => $request->input('precio3'), 'valor_limite' => $request->input('valor_limite3')],
                ];
                $this->valorDatosRegistrales_service->modificarValoresRegistrales($valoresRegistrales);

                Valor_registro_extra::query()->update([
                    'valor_extra' => $request->input('valor_registro_extra')
                ]);

                $mensajes[] = 'Valores registrales actualizados correctamente.';
            }

            // ... el resto de tus IFs siguen igual (pero usa $request->boolean() si puedes) ...

            if (empty($mensajes)) {
                return response()->json(['message' => 'No se seleccionó ninguna opción para actualizar.'], 400);
            }

            // RESPUESTA PARA VUE
            return response()->json([
                'status' => 'success',
                'message' => implode(' ', $mensajes)
            ], 200);
        } catch (\Exception $e) {
            // Si algo falla (conexión, base de datos, etc.), esto te dirá QUÉ es en Postman
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar los datos de calculo',
                'debug' => $e->getMessage(), // Esto te dirá el error real de la DB
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // Método para listar todos los registros
    /*     public function index()
    {
        $vistaNombre = 'sellado';

        // Verificar si el usuario tiene acceso a la vista
        $permiso = Permiso::where('usuario_id', $this->usuario->id)
            ->whereHas('vista', function ($query) use ($vistaNombre) {
                $query->where('vista_nombre', $vistaNombre);
            })
            ->first();

        if (!$permiso) {
            // Redirigir o mostrar un mensaje de error si no tiene acceso
            return redirect()->route('home')->with('error', 'No tienes acceso a esta vista.');
        }


        // Definimos un array con los nombres de los botones
        $btnNombres = [
            'datosDeCalculo',
            'acciones',
            'guardar'
        ];
        // Inicializamos un array vacío para almacenar los accesos
        $accesos = [];
        // Recorremos cada nombre de botón
        foreach ($btnNombres as $btnNombre) {
            // Verificamos si el usuario tiene acceso a cada botón y almacenamos el resultado en el array de accesos
        //    $accesos[$btnNombre] = $this->accessService->tieneAcceso($btnNombre);
        }
        // Asignamos el acceso a 'editarPadron'
        $tieneAccesoAcciones = $accesos['acciones'];
        $tieneAccesoDatosDeCalculo = $accesos['datosDeCalculo'];
        $tieneAccesoGuardar = $accesos['guardar'];
        $valor_registro_extra = Valor_registro_extra::all()->first()->valor_extra;

        $otrosValores = [
            'valorGastoAdminitrativo' => $this->valorGastoAdminitrativo_service->getAllValorGastoAdministrativo(),
            'valorHoja' => $this->valorHoja_service->getAllValorHoja(),
            'valorSellado' => $this->valorSellado_service->getAllValorSellado(),
            'valorDatosRegistrales' => $this->valorDatosRegistrales_service->getAllValorDatosRegistrales()
        ];

        session()->flash('valores', $otrosValores); // Guarda los otros valores en la sesión

        // Obtener los registros de sellado ordenados por los más recientes primero
        $valores = $this->registro_sellado->getAllRegistroSellado();

        return view('contable.sellado.index', compact('valores', 'valor_registro_extra', 'otrosValores', 'tieneAccesoAcciones', 'tieneAccesoDatosDeCalculo', 'tieneAccesoGuardar'));
    } */


    /* public function guardarDatosCalculo(Request $request)
    {
        $mensajes = [];

        if ($request->input('check_registro') == 1) {
            $valoresRegistrales = [
                ['id_valor_datos_registrales' => 1, 'precio' => $request->input('precio1'), 'valor_limite' => $request->input('valor_limite1')],
                ['id_valor_datos_registrales' => 2, 'precio' => $request->input('precio2'), 'valor_limite' => $request->input('valor_limite2')],
                ['id_valor_datos_registrales' => 3, 'precio' => $request->input('precio3'), 'valor_limite' => $request->input('valor_limite3')],
            ];
            $this->valorDatosRegistrales_service->modificarValoresRegistrales($valoresRegistrales);

            $valor_registro_extra = $request->input('valor_registro_extra');
            Valor_registro_extra::query()->update([
                'valor_extra' => $valor_registro_extra
            ]);


            $mensajes[] = 'Valores registrales actualizados correctamente.';
        }

        if ($request->input('check_valor_ga') == 1) {
            $valoresGastoAdministrativo = [
                ['id_valor_gasto_administrativo' => 1, 'tipo' => 1, 'valor' => $request->input('valor_con_cochera_6')],
                ['id_valor_gasto_administrativo' => 2, 'tipo' => 3, 'valor' => $request->input('valor_con_cochera_12')],
                ['id_valor_gasto_administrativo' => 3, 'tipo' => 0, 'valor' => $request->input('valor_con_base')],
            ];
            $this->valorGastoAdminitrativo_service->modificarValoresgastoAdministrativo($valoresGastoAdministrativo);
            $mensajes[] = 'Gastos administrativos actualizados correctamente.';
        }

        if ($request->input('check_precio_hoja') == 1) {
            $valorHoja = [
                ['id_valor_hoja' => 1, 'precio' => $request->input('precio_hoja')],
            ];
            $this->valorHoja_service->modificarValoresgastoAdministrativo($valorHoja);
            $mensajes[] = 'Valor de hoja actualizado correctamente.';
        }

        if ($request->input('check_sellado_valor') == 1) {
            $valorSellado = [
                ['id_valor_sellado' => 2, 'tipo' => 'vivienda', 'valor' => $request->input('sellado_vivienda_valor')],
                ['id_valor_sellado' => 3, 'tipo' => 'comercio', 'valor' => $request->input('sellado_comercio_valor')],
            ];
            $this->valorSellado_service->modificarValoresgastoAdministrativo($valorSellado);
            $mensajes[] = 'Valor de sellado actualizado correctamente.';
        }

        if (empty($mensajes)) {
            session()->flash('error', 'No se seleccionó ninguna opción para actualizar.');
        } else {
            session()->flash('success', implode(' ', $mensajes));
        }

        return back(); // Esto recarga la misma página sin redirigir a otra
    } */
}
