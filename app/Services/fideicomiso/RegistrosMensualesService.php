<?php

namespace App\Services\fideicomiso;

use App\Models\fideicomiso\RegistrosMensuales;
use Illuminate\Http\Request;

class RegistrosMensualesService
{
    public function __construct(
        protected RegistrosMensuales $registros
    ) {}

    // GET
    public function getRegistrosMensualesService()
    {
        return $this->registros->all();
    }

    // GET por ID
    public function getRegistroMensualPorIdService(int $id)
    {
        return $this->registros->findOrFail($id);
    }

    // GET por ID Unidad
    public function getRegistrosPorUnidadService(int $idUnidad)
    {
        return $this->registros->where('id_unidad', $idUnidad)->get();
    }

    // POST
   public function postRegistroMensualService(Request $request)
    {
        $registro = new RegistrosMensuales();
        $registro->tgi = $request->tgi;
        $registro->agua = $request->agua;
        $registro->api = $request->api;
        $registro->luz = $request->luz;
        $registro->seguro = $request->seguro;
        $registro->limpieza = $request->limpieza;
        $registro->ascensor = $request->ascensor;
        $registro->internet = $request->internet;
        $registro->honorario = $request->honorario;
        
        // --- NUEVOS CAMPOS INCORPORADOS AQUÍ ---
        $registro->extraordinaria = $request->extraordinaria;
        $registro->otro = $request->otro;
        $registro->otro_observaciones = $request->otro_observaciones;
        // ---------------------------------------

        $registro->periodo = $request->periodo;
        $registro->id_unidad = $request->id_unidad;
        
        // Si tu base de datos no tiene valor por defecto, asegurate de mandar el estado 'pagado'
        // $registro->pagado = $request->pagado ?? 'N'; 
        
        $registro->save();

        return $registro;
    }

  // PUT
    public function modificarRegistroMensualService(int $id, array $datos)
    {
        $registro = $this->registros->findOrFail($id);
        $registro->update($datos);
        return $registro;
    }

    // DELETE
    public function eliminarRegistroMensualService(int $id)
    {
        $registro = $this->registros->findOrFail($id);
        return $registro->delete();
    }
}