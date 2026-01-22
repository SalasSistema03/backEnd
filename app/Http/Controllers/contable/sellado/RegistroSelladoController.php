<?php

namespace App\Http\Controllers\Contable\Sellado;

use App\Http\Controllers\Controller; // Asegúrate de importar correctamente la clase base Controller

use App\Models\Contable\Sellado\Registro_sellado;
use App\Services\contable\sellado\RegistroSelladoService;
use App\Services\contable\sellado\ValorDatosRegistralesService;
use App\Services\contable\sellado\ValorGastoAdminitrativoService;
use App\Services\contable\sellado\ValorHojaService;
use App\Services\contable\sellado\ValorSelladoService;
use Illuminate\Http\Request;


class RegistroSelladoController extends Controller
{
    protected $registroSelladoService;


    protected $valorGastoAdminitrativo_service;

    protected $valorHoja_service;

    protected $valorSellado_service;

    protected $valorDatosRegistrales_service;
    protected $usuario_id;

    public function __construct(RegistroSelladoService $registroSelladoService, ValorGastoAdminitrativoService $valorGastoAdminitrativo, ValorHojaService $valorHoja, ValorSelladoService $valorSellado, ValorDatosRegistralesService $valorDatosRegistrales)
    {
        $this->registroSelladoService = $registroSelladoService;
        $this->valorGastoAdminitrativo_service = $valorGastoAdminitrativo;
        $this->valorHoja_service = $valorHoja;
        $this->valorSellado_service = $valorSellado;
        $this->valorDatosRegistrales_service = $valorDatosRegistrales;
    }

    // Método para listar todos los registros
    public function index() {}


    // Método para mostrar un registro específico
    public function show($id)
    {
        $valor = Registro_sellado::find($id);

        if ($valor) {
            return response()->json($valor);
        }

        return response()->json(['message' => 'Registro no encontrado'], 404);
    }


    // Método para actualizar un registro existente
    public function update(Request $request, $id)
    {
        $valor = Registro_sellado::find($id);

        if ($valor) {
            $valor->update($request->all());
            return response()->json($valor);
        }

        return response()->json(['message' => 'Registro no encontrado'], 404);
    }

    // Método para eliminar un registro
    public function destroy(Request $request)
    {
        $eliminar = $request->input('check_eliminar');
        if ($eliminar == 1) {
            Registro_sellado::truncate(); // Elimina todos los registros de la tabla
            return redirect()->back()->with('success', 'Todos los registros han sido eliminados.');
        } else {
            return redirect()->back()->with('error', 'No se ha marcado la casilla para eliminar.');
        }
    }


    public function calculoSellado(Request $request)
{
    try {
        session()->flash('openModal', true);

        // Validar los datos y calcular el resultado
        $data = $request->validate([
            'folio'             => 'required|string',
            'cantidad_meses'    => 'required|integer',
            'monto_alquiler'    => 'required|numeric',
            'monto_documento'   => 'nullable|numeric',
            'nombre'            => 'required|string',
            'monto_contrato'    => 'nullable|numeric',
            'hojas'             => 'required|integer',
            'informe'           => 'string|in:SI,NO',
            'cantidad_informes' => 'nullable|integer',
            'tipo_contrato'     => 'string',
            'inq_prop'          => 'string',
            'fecha_inicio'      => 'required|date',
        ]);

        $resultado = $this->registroSelladoService->calculoRegistroSelladoResultado($data);

        $resultado = $resultado ?? [
            'folio' => null,
            'nombre' => null,
            'cantidad_meses' => null,
            'monto_alquiler' => null,
            'monto_documento' => null,
            'monto_contrato' => null,
            'hojas' => null,
            'informe' => null,
            'cantidad_informes' => null,
            'tipo_contrato' => null,
            'inq_prop' => null,
            'fecha_inicio' => null,
            'gasto_administrativo' => null,
            'iva_gasto_adm' => null,
            'sellado' => null,
            'valor_informe' => null,
            'monto_alquiler_comercial' => null,
            'monto_alquiler_vivienda' => null,
            'prop_alquiler' => null,
            'prop_doc' => null,
            'total_contrato' => null,
            'fecha_carga' => null,
            'usuario_id' => null,
        ];

        session()->put('resultado', $resultado);

        return redirect()->back()->with('success', 'Cálculo realizado correctamente.');
    } catch (\Throwable $e) {
        // Log del error si querés
        // Log::error($e);

        return redirect()->back()->with('error', 'Ocurrió un error al calcular el sellado.');
    }
}



    public function guardar_registroSellado()
    {
        $datos = session('resultado'); // Recuperar los datos de la sesión

        if (!$datos || !is_array($datos)) {
            return redirect()->back()->with('error', 'No hay datos válidos para guardar.');
        }

        Registro_sellado::create($datos); // Guardar en la base de datos

        session()->forget('resultado'); // Eliminar la sesión después de guardar

        return redirect()->back()->with('success', 'Registro guardado correctamente.');
    }



    public function exportarRegistroSellado()
    {
        // Obtener los datos de la tabla `registro_sellado`
        $registros = Registro_sellado::all();

        // Crear el contenido del archivo .txt
        /* $contenido = "ID\tNombre\tDescripción\tFecha\n";  */
        $contenido = "";  // Puedes personalizar los encabezados

        foreach ($registros as $registro) {

           /* dd($registro); */
            $monto_vivienda = $registro->monto_alquiler_vivienda * $registro->cantidad_meses;
            if($registro->inq_prop == 'SI'){
                $monto_comercial = $registro->monto_alquiler_comercial * $registro->cantidad_meses;
            }else{
                $monto_comercial = $registro->monto_alquiler_comercial * $registro->cantidad_meses*1.21;
            }


            $contenido .= "{$registro->folio};{$registro->nombre};{$registro->fecha_inicio};{$registro->tipo_contrato};{$monto_vivienda};{$monto_comercial};{$registro->hojas};{$registro->fecha_carga};{$registro->inq_prop}\n";  // Reemplaza con tus columnas
            
            /* $contenido .= "{$registro->folio};{$registro->nombre};{$registro->fecha_inicio};{$registro->tipo_contrato};{$registro->monto_alquiler_vivienda};{$registro->monto_alquiler_comercial};{$registro->hojas};{$registro->fecha_carga};{$registro->usuario_id}\n";  // Reemplaza con tus columnas */
        }
        
        /* dd($registros); */
        $fechaHora = now()->format('Y-m-d_H-i-s');

        // Definir el nombre del archivo
        $nombreArchivo = 'registro_sellado_' . $fechaHora . '.txt';

        // Retornar el archivo para su descarga
        return response($contenido)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
    }
}
