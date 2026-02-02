<?php

namespace App\Http\Controllers\contable\sellado;

use App\Http\Controllers\Controller; // Asegúrate de importar correctamente la clase base Controller
use App\Models\At_cl\Usuario;
use Illuminate\Http\Request;

use App\Models\contable\sellado\Registro_sellado;
use App\Models\usuarios_y_permisos\Permiso;
use App\Services\contable\sellado\RegistroSelladoService;
use App\Services\contable\sellado\ValorDatosRegistralesService;
use App\Services\contable\sellado\ValorGastoAdminitrativoService;
use App\Services\contable\sellado\ValorHojaService;
/* use App\Models\Contable\Sellado\Valor_hoja;

use App\Models\Contable\Sellado\Valor_datos_registrales;
use App\Models\Contable\Sellado\Valor_gasto_administrativo; */
use App\Services\contable\sellado\ValorSelladoService;
use App\Services\contable\sellado\PermitirAccesoSelladoService;
use App\Models\contable\sellado\Valor_registro_extra;


class SelladoController extends Controller
{
    private RegistroSelladoService $registro_sellado;

    protected $valorGastoAdminitrativo_service;

    protected $valorHoja_service;

    protected $valorSellado_service;

    protected $valorDatosRegistrales_service;

    protected $usuario;

    protected $usuario_id;

    protected $accessService;

    public function __construct(RegistroSelladoService $registro_sellado, ValorGastoAdminitrativoService $valorGastoAdminitrativo, ValorHojaService $valorHoja, ValorSelladoService $valorSellado, ValorDatosRegistralesService $valorDatosRegistrales)
    {
        $this->registro_sellado = $registro_sellado;
        $this->valorGastoAdminitrativo_service = $valorGastoAdminitrativo;
        $this->valorHoja_service = $valorHoja;
        $this->valorSellado_service = $valorSellado;
        $this->valorDatosRegistrales_service = $valorDatosRegistrales;
        $this->usuario_id = session('usuario_id'); // Obtener el id del usuario actual desde la sesión
        $this->accessService = new PermitirAccesoSelladoService($this->usuario_id);
    }


    //Este controlador retorna la infromacion del sellado
    public function getDatosSelladoController(Request $request)
    {
        // 2. Recopilación de Permisos de Botones
        $botones = [
            'datosDeCalculo' => $this->accessService->tieneAcceso('datosDeCalculo'),
            'acciones'       => $this->accessService->tieneAcceso('acciones'),
            'guardar'        => $this->accessService->tieneAcceso('guardar')
        ];

        // 3. Obtención de Datos
        $valores = Registro_sellado::latest()->get();
        $valor_registro_extra = Valor_registro_extra::first()->valor_extra ?? 0;

        $otrosValores = [
            'valorGastoAdministrativo' => $this->valorGastoAdminitrativo_service->getAllValorGastoAdministrativo(),
            'valorHoja'                => $this->valorHoja_service->getAllValorHoja(),
            'valorSellado'             => $this->valorSellado_service->getAllValorSellado(),
            'valorDatosRegistrales'    => $this->valorDatosRegistrales_service->getAllValorDatosRegistrales()
        ];
        // 4. Respuesta JSON estructurada
        return response()->json([
            'data' => [
                'registros' => $valores,
                'configuracion' => [
                    'valor_extra' => $valor_registro_extra,
                    'otros' => $otrosValores
                ]
            ],
            'permissions' => $botones // El frontend usará esto para mostrar/ocultar botones
        ], 200);
    
        
    }


 
    // Método para listar todos los registros
    public function index()
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
            $accesos[$btnNombre] = $this->accessService->tieneAcceso($btnNombre);
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
        
        return view('contable.sellado.index', compact('valores','valor_registro_extra', 'otrosValores','tieneAccesoAcciones','tieneAccesoDatosDeCalculo','tieneAccesoGuardar'));
    }


    public function guardarDatosCalculo(Request $request)
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
    }
}
