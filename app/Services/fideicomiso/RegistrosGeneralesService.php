<?php

namespace App\Services\fideicomiso;

use App\Models\fideicomiso\RegistrosGenerales;
use App\Models\fideicomiso\RegistrosMensuales;
use App\Models\fideicomiso\Unidades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RegistrosGeneralesService
{
    // GET
    public function getRegistrosGeneralesService()
    {
        return RegistrosGenerales::all();
    }

    // GET
    public function getRegistroGeneralPorIdService(int $id)
    {
        return RegistrosGenerales::findOrFail($id);
    }

    // POST
    public function postRegistroGeneralService(Request $request)
    {
        $registro = new RegistrosGenerales();
        $registro->tgi = $request->tgi;
        $registro->agua = $request->agua;
        $registro->api = $request->api;
        $registro->luz = $request->luz;
        $registro->seguro = $request->seguro;
        $registro->limpieza = $request->limpieza;
        $registro->ascensor = $request->ascensor;
        $registro->honorario = $request->honorario;
        $registro->periodo = $request->periodo;
        $registro->vencimiento = $request->vencimiento;
        $registro->save();

        return $registro;
    }

    // PUT
    public function modificarRegistroGeneralService(int $id, array $datos)
    {
        $registro = RegistrosGenerales::findOrFail($id);
        $registro->update($datos);
        return $registro;
    }

    // DELETE
    public function eliminarRegistroGeneralService(int $id)
    {
        $registro = RegistrosGenerales::findOrFail($id);
        return $registro->delete();
    }


    public function procesarCargaMasiva($periodo, $servicio, $montoTotal)
    {
        // Iniciamos la transacción para asegurar la integridad de los datos
        DB::beginTransaction();

        try {
            // 1. REGISTRO GENERAL: Buscamos si existe el período, si no, lo instanciamos
            // Usamos firstOrNew para no tener que hacer un if gigante
            $registroGeneral = RegistrosGenerales::firstOrNew(['periodo' => $periodo]);
            
            // Asignamos el monto al campo dinámico (ej: $registroGeneral->tgi = 150000)
            $registroGeneral->$servicio = $montoTotal;
            $registroGeneral->save();

            // 2. UNIDADES: Traemos todas las unidades para sacar sus porcentajes
            $unidades = Unidades::all();

            // 3. REGISTROS MENSUALES: Recorremos cada unidad y repartimos
            foreach ($unidades as $unidad) {
                $montoProporcional = $montoTotal * ($unidad->porcentual / 100);

                // Buscamos si ya tiene registro para ese período, si no lo creamos
                $registroMensual = RegistrosMensuales::firstOrNew([
                    'periodo' => $periodo,
                    'id_unidad' => $unidad->id
                ]);

                // Asignamos el monto y guardamos
                $registroMensual->$servicio = $montoProporcional;
                $registroMensual->save();
            }

            // Si todo salió bien, confirmamos los cambios en la DB
            DB::commit();
            return true;

        } catch (\Exception $e) {
            // Si algo falla, revertimos todos los inserts/updates
            DB::rollBack();
            throw $e;
        }
    }
}