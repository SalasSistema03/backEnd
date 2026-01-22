<?php

namespace App\Http\Controllers\Contable\Sellado;

use App\Http\Controllers\Controller; // Asegúrate de importar correctamente la clase base Controller

use App\Models\Contable\Sellado\Valor_hoja;
use Illuminate\Http\Request;

class ValorHojaController extends Controller
{
    // Método para listar todos los registros
    public function index()
    {
        $valores = Valor_hoja::all();
        return view('contable.sellado.index', compact('valores'));
        //return response()->json($valores);
    }

    // Método para mostrar un registro específico
    public function show($id)
    {
        $valor = Valor_hoja::find($id);

        if ($valor) {
            return response()->json($valor);
        }

        return response()->json(['message' => 'Registro no encontrado'], 404);
    }

    // Método para crear un nuevo registro
    public function store(Request $request)
    {
        $request->validate([
            'campo1' => 'required', // Ajusta según tus campos
            'campo2' => 'required'
        ]);

        $valor = Valor_hoja::create($request->all());
        return response()->json($valor, 201);
    }

    // Método para actualizar un registro existente
    public function update(Request $request, $id)
    {
        $valor = Valor_hoja::find($id);

        if ($valor) {
            $valor->update($request->all());
            return response()->json($valor);
        }

        return response()->json(['message' => 'Registro no encontrado'], 404);
    }

    // Método para eliminar un registro
    public function destroy($id)
    {
        $valor = Valor_hoja::find($id);

        if ($valor) {
            $valor->delete();
            return response()->json(['message' => 'Registro eliminado correctamente']);
        }

        return response()->json(['message' => 'Registro no encontrado'], 404);
    }
}
