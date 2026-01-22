<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'zona'.
     *
     * Este método crea la tabla 'zona' en la base de datos, que incluye un 
     * campo para el identificador único de cada zona (clave primaria auto-incremental) 
     * y un campo para almacenar el nombre de la zona. Además, incluye las columnas 
     * de marcas de tiempo 'created_at' y 'updated_at', que Laravel maneja automáticamente.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'zona' con las columnas 'id' y 'name'
        Schema::create('zona', function (Blueprint $table) {
            $table->id(); // Columna 'id' como clave primaria auto-incremental
            $table->string('name'); // Columna 'name' para almacenar el nombre de la zona
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' para marcas de tiempo automáticas
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'zona'.
     *
     * Este método define las acciones a ejecutar cuando se hace rollback de 
     * la migración usando el comando `php artisan migrate:rollback`. Elimina 
     * la tabla 'zona' de la base de datos si existe.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'zona' si existe
        Schema::dropIfExists('zona');
    }
};
