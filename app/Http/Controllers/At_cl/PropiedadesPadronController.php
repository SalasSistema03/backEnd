<?php

namespace App\Http\Controllers\At_cl;

use App\Models\At_cl\Padron;
use Illuminate\Http\Request;
use App\Models\At_cl\Propiedades_padron;
use Illuminate\Support\Facades\DB;

class PropiedadesPadronController
{
    protected $tipo_inmueble, $zona, $calle, $estado_alquileres, $estado_general,
        $estado_venta, $localidad, $barrio, $Propiedades, $contrato_cabecera, $observaciones_propiedades, $provincia,
        $padron, $propiedadPadron;

    public function __construct()
    {
        // Definir variables globales para todas las funciones
        $this->padron = Padron::all();
        $this->propiedadPadron = Propiedades_padron::all();
    }
    public function index()
    {
        return view('atencionAlCliente.propiedad.cargaPropietario', [
            'padron' => $this->padron,
            'propiedadPadron' => $this->propiedadPadron
        ]);
    }

    public function create() {}

    public function store(Request $request) {}

    public function show(string $id) {}

    public function edit(string $id) {}

    public function update(Request $request, string $id) {}

    public function destroy(string $id) {}


    public function vincular(Request $request)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Crear la relación en la tabla intermedia
            Propiedades_padron::create([
                'propiedad_id' => $request->propiedad_id,
                'padron_id' => $request->padron_id,
                // Aquí podrías agregar last_modified_by si tenés autenticación
            ]);

            DB::commit(); // Confirmar la operación
            return response()->json([
                'success' => true,
                'message' => 'Persona vinculada correctamente a la propiedad.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios si algo falla
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al vincular.'
            ], 500);
        }
    }


    public function desvincular(Request $request)
    {
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Eliminar la relación entre propiedad y padrón
            $deleted = DB::table('propiedades_padron')
                ->where('propiedad_id', $request->propiedad_id)
                ->where('padron_id', $request->padron_id)
                ->delete();

            if ($deleted) {
                DB::commit(); // Confirmar operación si se eliminó
                return response()->json([
                    'success' => true,
                    'message' => 'La persona fue desvinculada correctamente de la propiedad.'
                ]);
            } else {
                DB::rollBack(); // Revertir si no se encontró nada para eliminar
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una relación para eliminar.'
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir en caso de error
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al desvincular.'
            ], 500);
        }
    }
}
