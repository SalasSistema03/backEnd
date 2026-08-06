<?php

namespace App\Services\fideicomiso;

use App\Models\fideicomiso\Unidades;
use Illuminate\Database\Eloquent\Collection;

class UnidadesService
{
    public function obtenerTodas(): Collection
    {
        return Unidades::all();
    }

    public function obtenerPorId(int $id): ?Unidades
    {
        return Unidades::find($id);
    }

    public function guardar(array $data): Unidades
    {
        return Unidades::create($data);
    }

    public function actualizar(int $id, array $data): ?Unidades
    {
        $unidad = Unidades::find($id);
        if ($unidad) {
            $unidad->update($data);
        }
        return $unidad;
    }

    public function eliminar(int $id): bool
    {
        $unidad = Unidades::find($id);
        if ($unidad) {
            return $unidad->delete();
        }
        return false;
    }
}