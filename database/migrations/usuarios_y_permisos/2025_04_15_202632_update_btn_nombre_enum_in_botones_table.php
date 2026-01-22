<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql4";

    public function up(): void
    {
        // Modificar el campo enum para agregar nuevos valores
        DB::connection($this->connection)->statement("
            ALTER TABLE botones MODIFY btn_nombre ENUM(
                'propietario',
                'informacion_venta',
                'informacion_alquiler',
                'modificar',
                'editarPadron',
                'acciones',
                'datosDeCalculo',
                'guardar'
            )
        ");
    }

    public function down(): void
    {
        // Revertir el campo enum a los valores originales
        DB::connection($this->connection)->statement("
            ALTER TABLE botones MODIFY btn_nombre ENUM(
                'propietario',
                'informacion_venta',
                'informacion_alquiler',
                'modificar',
                'editarPadron'
            )
        ");
    }
};