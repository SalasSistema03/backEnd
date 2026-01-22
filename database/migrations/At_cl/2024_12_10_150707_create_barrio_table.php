<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'barrio'.
     *
     * Este método crea la tabla 'barrio' en la base de datos, que incluye:
     * un campo para el identificador único de cada barrio (clave primaria auto-incremental) 
     * y un campo para almacenar el nombre del barrio. Además, se incluyen las columnas 
     * 'created_at' y 'updated_at' que Laravel maneja automáticamente.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'barrio' con las columnas 'id' y 'name'
        Schema::create('barrio', function (Blueprint $table) {
            $table->id(); // Columna 'id' como clave primaria auto-incremental
            $table->string('name'); // Columna 'name' para almacenar el nombre del barrio
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' para marcas de tiempo automáticas
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'barrio'.
     *
     * Este método define las acciones a ejecutar cuando se hace rollback de la migración 
     * utilizando el comando `php artisan migrate:rollback`. Elimina la tabla 'barrio' 
     * de la base de datos si existe.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'barrio' si existe
        Schema::dropIfExists('barrio');
    }
};
