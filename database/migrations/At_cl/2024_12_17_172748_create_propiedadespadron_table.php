<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'propiedades_padron' que establece una relación entre 
 * las tablas 'propiedades' y 'padron'.
 * 
 * Esta tabla será una tabla intermedia que almacena las relaciones entre las propiedades
 * y las personas del padrón. Contendrá:
 * - Un identificador único 'id' auto-incremental.
 * - Las claves foráneas que vinculan a las tablas 'propiedades' y 'padron'.
 * 
 * @package Database\Migrations
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'propiedades_padron'.
     * 
     * Este método crea la tabla intermedia 'propiedades_padron', que contiene:
     * - Un identificador único 'id' auto-incremental.
     * - Las claves foráneas 'propiedad_id' y 'padron_id', que se refieren a las tablas 'propiedades' y 'padron', respectivamente.
     * 
     * @return void
     */
    public function up(): void
    {
        // Crear la tabla 'propiedades_padron' para la relación muchos a muchos
        Schema::create('propiedades_padron', function (Blueprint $table) {
            $table->id(); // Clave primaria auto-incremental

            // Definir las claves foráneas que referencian las tablas 'propiedades' y 'padron'
            $table->unsignedBigInteger('propiedad_id'); // ID de la propiedad
            $table->unsignedBigInteger('padron_id'); // ID del padrón
            $table->string('observaciones')->nullable();
            // Definir las restricciones de clave foránea
            $table->foreign('propiedad_id')->references('id')->on('propiedades')->onDelete('cascade');
            $table->foreign('padron_id')->references('id')->on('padron')->onDelete('cascade');
            $table->enum('baja', ['si', 'no'])->default('no'); // Estado de baja (si/no)
            $table->date('fecha_baja')->nullable(); // Fecha de baja
            $table->text('observaciones_baja')->nullable(); // Observaciones de la baja
            $table->unsignedBigInteger('last_modified_by')->nullable();
            /* $table->foreign('last_modified_by')->references('id')->on('usuarios')->nullOnDelete(); */
            // Añadir las marcas de tiempo 'created_at' y 'updated_at'
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración, eliminando la tabla 'propiedades_padron'.
     * 
     * Este método elimina la tabla 'propiedades_padron' cuando se hace rollback de la migración.
     *
     * @return void
     */
    public function down(): void
    {
        // Eliminar la tabla 'propiedades_padron' si existe
        Schema::dropIfExists('propiedades_padron');
    }
};
