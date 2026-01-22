<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'provincia'.
     *
     * Este método se encarga de crear la tabla 'provincia' en la base de datos,
     * que incluye un campo para el identificador único de cada provincia
     * (clave primaria auto-incremental) y un campo para almacenar el nombre 
     * de la provincia. Además, incluye las columnas de marcas de tiempo 
     * 'created_at' y 'updated_at', que Laravel maneja automáticamente.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'provincia' con las columnas 'id' y 'name'
        Schema::create('provincia', function (Blueprint $table) {
            $table->id(); // Columna 'id' como clave primaria auto-incremental
            $table->string('name'); // Columna 'name' para almacenar el nombre de la provincia
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' para marcas de tiempo automáticas
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'provincia'.
     *
     * Este método define las acciones a ejecutar cuando se hace rollback 
     * de la migración con `php artisan migrate:rollback`. Elimina la tabla 
     * 'provincia' de la base de datos.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'provincia' si existe
        Schema::dropIfExists('provincia');
    }
};
