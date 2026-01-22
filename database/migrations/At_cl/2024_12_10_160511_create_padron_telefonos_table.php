<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla 'padron_telefonos'.
     *
     * Este método crea la tabla 'padron_telefonos' en la base de datos, la cual almacena 
     * los números de teléfono asociados a los registros de la tabla 'padron'. 
     * La tabla incluye los siguientes campos:
     * - Un identificador único 'id' auto-incremental.
     * - El campo 'phone_number' para almacenar el número de teléfono del individuo.
     * - El campo 'notes' para almacenar notas adicionales sobre el teléfono.
     * - Un campo 'padron_id' que actúa como una clave foránea que se relaciona con la tabla 'padron'.
     * - Se generan las columnas 'created_at' y 'updated_at' automáticamente por Laravel.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'padron_telefonos' con las columnas definidas
        Schema::create('padron_telefonos', function (Blueprint $table) {
            $table->id(); // Clave primaria auto-incremental
            $table->text('phone_number')->nullable(); // Número de teléfono
            $table->string('notes')->nullable(); // Notas adicionales sobre el teléfono
            $table->foreignId('padron_id') // Clave foránea relacionada con 'padron'
                  ->constrained('padron') // Establece la relación con la tabla 'padron'
                  ->cascadeOnDelete()->nullable(); // Si se elimina un registro en 'padron', se eliminarán los teléfonos asociados
            $table->unsignedBigInteger('last_modified_by')->nullable();
            $table->timestamps(); // Marca de tiempo automática para 'created_at' y 'updated_at'
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'padron_telefonos'.
     *
     * Este método elimina la tabla 'padron_telefonos' si existe en la base de datos, 
     * lo que es útil cuando se realiza un rollback de la migración.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'padron_telefonos' si existe
        Schema::dropIfExists('padron_telefonos');
    }
};
