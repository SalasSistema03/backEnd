<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'inmueble'.
     *
     * Este método crea la tabla 'inmueble' en la base de datos. La tabla incluye:
     * - Un campo 'id' auto-incremental como clave primaria.
     * - Un campo 'inmueble' que almacena el nombre o descripción del inmueble.
     * - Campos 'created_at' y 'updated_at' que se gestionan automáticamente.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'inmueble' con los campos definidos
        Schema::create('tipo_inmueble', function (Blueprint $table) {
            $table->id(); // Clave primaria auto-incremental
            $table->string('inmueble'); // Nombre o descripción del inmueble
            $table->timestamps(); // Marca de tiempo automática para 'created_at' y 'updated_at'
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'inmueble'.
     *
     * Este método elimina la tabla 'inmueble' en caso de que sea necesario revertir la migración.
     * Utiliza este método cuando se realiza un rollback.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'inmueble' si existe
        Schema::dropIfExists('inmueble');
    }
};
