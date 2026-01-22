<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql4';
    /**
     * Ejecuta la migración para crear la tabla 'usuarios'.
     *
     * Este método crea la tabla 'usuarios' en la base de datos, que incluye:
     * un campo para el identificador único del usuario (clave primaria auto-incremental),
     * un campo para almacenar el nombre del usuario y un campo para almacenar la contraseña.
     * Además, se incluyen las columnas 'created_at' y 'updated_at' que Laravel maneja automáticamente.
     *
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'usuarios' con las columnas 'id', 'name', 'password' y las columnas de marcas de tiempo
        Schema::connection($this->connection)->create('usuarios', function(Blueprint $table){
            $table->id(); // Clave primaria auto-incremental
            $table->string('name'); // Columna para almacenar el nombre del usuario
            $table->string('username')->unique(); // Agrega el campo 'username' después de 'name'
            $table->string('password'); // Columna para almacenar la contraseña del usuario (considerar cifrado)
            $table->boolean('admin')->default('0'); // Columna para indicar si el usuario es administrador (0 o 1)
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' para marcas de tiempo automáticas
        });
    }

    /**
     * Revierte la migración eliminando la tabla 'usuarios'.
     *
     * Este método define las acciones a ejecutar cuando se hace rollback de la migración 
     * utilizando el comando `php artisan migrate:rollback`. Elimina la tabla 'usuarios' 
     * de la base de datos si existe.
     *
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'usuarios' si existe
        Schema::dropIfExists('usuarios');
    }
};
