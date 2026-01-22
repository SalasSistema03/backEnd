<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'calle'.
     *
     * Este método se encarga de crear la tabla 'calle' en la base de datos,
     * que incluye un campo para el identificador único de cada calle 
     * (clave primaria auto-incremental) y un campo para almacenar el nombre 
     * de la calle. También incluye las columnas de marcas de tiempo
     * 'created_at' y 'updated_at', que se gestionan automáticamente 
     * mediante Laravel.
     * 
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'calle' con las columnas 'id' y 'name'
        Schema::create('calle', function (Blueprint $table) {
            $table->id(); // Columna 'id' como clave primaria auto-incremental
            $table->string('name'); // Columna 'name' para almacenar el nombre de la calle
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' para marcas de tiempo automáticas
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'calle'.
     *
     * Este método define las acciones a ejecutar cuando se hace rollback 
     * de la migración con `php artisan migrate:rollback`. Elimina la tabla 
     * 'calle' de la base de datos.
     * 
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'calle' si existe
        Schema::dropIfExists('calle');
    }
};
