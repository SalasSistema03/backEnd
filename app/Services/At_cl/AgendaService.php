<?php

namespace App\Services\At_cl\Agenda;

use App\Models\cliente\clientes;
use App\Models\agenda\Notas;
use Carbon\Carbon;

class ClienteBusquedaService
{
    /**
     * Busca clientes por teléfono y agrega información de la última nota
     */
    public function buscarPorTelefono(string $telefono, int $limit = 10)
    {
        $clientes = clientes::where('telefono', 'like', "%{$telefono}%")
            ->limit($limit)
            ->get(['id_cliente', 'nombre', 'telefono']);

        foreach ($clientes as $cliente) {

            $ultimaNota = Notas::with('usuario')
                ->where('cliente_id', $cliente->id_cliente)
                ->orderBy('fecha', 'desc')
                ->first(['id', 'propiedad_id', 'fecha', 'usuario_id']);

            // Merge de datos
            $cliente->propiedad_id = $ultimaNota->propiedad_id ?? null;
            $cliente->fecha_ultima_nota = $ultimaNota->fecha ?? null;
            $cliente->usuario = $ultimaNota->usuario->name ?? null;

            // Meses desde la última nota (mínimo 1)
            if ($ultimaNota && $ultimaNota->fecha) {
                $meses = Carbon::parse($ultimaNota->fecha)->diffInMonths(now());
                $cliente->meses_desde_ultima_nota = max(1, $meses);
            } else {
                $cliente->meses_desde_ultima_nota = null;
            }
        }

        return $clientes;
    }
}
