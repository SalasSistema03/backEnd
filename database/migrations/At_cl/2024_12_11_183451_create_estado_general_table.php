<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'estado_general'.
     *
     * Este método crea la tabla 'estado_general' en la base de datos, que almacena información 
     * sobre los diferentes estados generales. La tabla incluye los siguientes campos:
     * - Un identificador único 'id' auto-incremental.
     * - Un campo 'estado_general' que almacena el nombre del estado general.
     * - Se generan las columnas 'created_at' y 'updated_at' automáticamente por Laravel.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'estado_general' con las columnas definidas
        Schema::create('estado_general', function (Blueprint $table) {
            $table->id(); // Clave primaria auto-incremental
            $table->string('estado_general'); // Nombre del estado general
            $table->timestamps(); // Marca de tiempo automática para 'created_at' y 'updated_at'
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'estado_general'.
     *
     * Este método elimina la tabla 'estado_general' si existe en la base de datos, 
     * lo que es útil cuando se realiza un rollback de la migración.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'estado_general' si existe
        Schema::dropIfExists('estado_general');
    }
};
