<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = "mysql4";

    public function up(): void
    {
        // Modificar el campo enum para agregar el nuevo valor 'sellado' en vista_nombre
        DB::connection($this->connection)->statement("
            ALTER TABLE vistas MODIFY vista_nombre ENUM(
                'propiedadBuscar',
                'propiedadCargar',
                'propiedadpadronCargar',
                'propiedad',
                'propiedadEditar',
                'propiedadEditarFotos',
                'propiedadEditarDocumentos',
                'padronBuscar',
                'padronCargar',
                'padronEditar',
                'padron',
                'sellado'
            )
        ");

        // Modificar el campo enum para agregar el nuevo valor 'Sellado' en Seccion
        DB::connection($this->connection)->statement("
            ALTER TABLE vistas MODIFY Seccion ENUM(
                'Propiedad',
                'Cliente',
                'Padron',
                'Sellado'
            )
        ");
    }

    public function down(): void
    {
        // Revertir el campo enum de vista_nombre a los valores originales
        DB::connection($this->connection)->statement("
            ALTER TABLE vistas MODIFY vista_nombre ENUM(
                'propiedadBuscar',
                'propiedadCargar',
                'propiedadpadronCargar',
                'propiedad',
                'propiedadEditar',
                'propiedadEditarFotos',
                'propiedadEditarDocumentos',
                'padronBuscar',
                'padronCargar',
                'padronEditar',
                'padron',
                'sellado'
            )
        ");

        // Revertir el campo enum de Seccion a los valores originales
        DB::connection($this->connection)->statement("
            ALTER TABLE vistas MODIFY Seccion ENUM(
                'Propiedad',
                'Cliente',
                'Padron'
            )
        ");
    }
};