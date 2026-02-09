<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Padron;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PadronService
{
    /**
     * Buscar personas en el padrón según apellido o DNI.
     *
     * @param string|null $apellido  Apellido a buscar.
     * @param string|null $dni       DNI a buscar.
     * @return \Illuminate\Support\Collection
     */
    public function BuscarPadron(Request $request)
    {
        $apellido = $request->query('apellido');
        $dni = $request->query('dni');

        if (!$apellido && !$dni) {
            return response()->json([]);
        }

        $personas = Padron::with('telefonos')
            ->when($apellido, function ($q) use ($apellido) {
                $q->where('apellido', 'like', "%{$apellido}%");
            })
            ->when($dni, function ($q) use ($dni) {
                $q->where('documento', 'like', "%{$dni}%");
            })
            ->get();


        foreach ($personas as $persona) {
            $persona->fecha_nacimiento = Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y');
        }

        return response()->json($personas);
    }

    public function CargarPadron($padron)
    {
        $padronCreado = Padron::create([
            'nombre' => strtoupper($padron->nombre),
            'apellido' => strtoupper($padron->apellido),
            'documento' => $padron->dni,
            'fecha_nacimiento' => $padron->fecha_nacimiento,
            'calle' => strtoupper($padron->calle),
            'numero_calle' => $padron->numero_calle,
            'piso_departamento' => $padron->piso,
            'ciudad' => strtoupper($padron->ciudad),
            'provincia' => strtoupper($padron->ciudad),
            'notes' => strtoupper($padron->comentarios),
            'last_modified_by' => $padron->usuario_id,

        ]);

        $padronTelefonosService = new PadronTelefonosService();
        $padronTelefonosService->CargarTelefonos($padron, $padronCreado->id); 
    }
}
