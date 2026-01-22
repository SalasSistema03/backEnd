<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'padron'.
     *
     * Este método crea la tabla 'padron' en la base de datos, la cual almacena la información 
     * personal y de ubicación de una persona. La tabla incluye:
     * - Un identificador único 'id' auto-incremental.
     * - Campos para almacenar los nombres, fecha de nacimiento, dirección, ciudad, estado y notas opcionales.
     * Además, se generan automáticamente las columnas 'created_at' y 'updated_at' para las marcas de tiempo.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'padron' con las columnas definidas
        Schema::create('padron', function (Blueprint $table) {
            $table->id(); // Clave primaria auto-incremental
            $table->string('nombre', 100); // Nombre del individuo (máximo 100 caracteres)
            $table->string('apellido', 100); // Apellido del individuo (máximo 100 caracteres)
            $table->string('documento', 100)->nullable(); // Documento del individuo, puede ser nula
            $table->date('fecha_nacimiento')->nullable(); // Fecha de nacimiento del individuo, puede ser nula
            $table->string('calle', 100)->nullable(); // Calle del individuo, puede ser nula
            $table->integer('numero_calle')->nullable(); // Número de la calle, puede ser nulo
            $table->string('piso_departamento', 100)->nullable(); // Piso o apartamento, puede ser nulo
            $table->string('ciudad', 100)->nullable(); // Ciudad del individuo, puede ser nula
            $table->string('provincia', 100)->nullable(); // Estado o provincia, puede ser nulo
            $table->text('notes')->nullable(); // Notas adicionales, pueden ser nulas
            $table->timestamps(); // Marca de tiempo automática para 'created_at' y 'updated_at'
            $table->unsignedBigInteger('last_modified_by')->nullable();
            
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'padron'.
     *
     * Este método define lo que sucede cuando se hace rollback de la migración 
     * utilizando el comando `php artisan migrate:rollback`. Elimina la tabla 'padron' 
     * de la base de datos si existe.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'padron' si existe
        Schema::dropIfExists('padron');
    }
};
