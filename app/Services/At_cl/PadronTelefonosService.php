<?php

namespace App\Services\At_cl;

use App\Models\At_cl\Padron;
use App\Models\At_cl\Padron_telefonos;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PadronTelefonosService
{
    public function CargarTelefonos($padron, $padronId)
    {
       
        $telefonos = json_decode($padron->telefonos, true);
        $usuario = json_decode($padron->usuario_id, true);
        
        if (is_array($telefonos)) {
            foreach ($telefonos as $telefono) {
                Padron_telefonos::create([
                    'phone_number' => $telefono['phone_number'],
                    'notes' => $telefono['notes'] ?? null,
                    'last_modified_by' => $usuario,
                    'padron_id' => $padronId,
                ]);
            }
        }
    }
}


