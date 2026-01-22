<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para crear las tablas de cache y cache_locks.
     *
     * Este método define las tablas necesarias para gestionar el almacenamiento de caché
     * y los bloqueos de caché en la base de datos. Se ejecuta cuando se corre la migración 
     * con `php artisan migrate`.
     * 
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'cache' para almacenar claves de caché y sus valores
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary(); // Crea una columna 'key' como clave primaria
            $table->mediumText('value'); // Crea una columna 'value' para almacenar el valor de caché
            $table->integer('expiration'); // Crea una columna 'expiration' para almacenar el tiempo de expiración del caché (timestamp)
        });

        // Crea la tabla 'cache_locks' para gestionar los bloqueos de caché
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary(); // Crea una columna 'key' como clave primaria para identificar el bloqueo
            $table->string('owner'); // Crea una columna 'owner' para almacenar el propietario del bloqueo
            $table->integer('expiration'); // Crea una columna 'expiration' para almacenar el tiempo de expiración del bloqueo (timestamp)
        });
    }

    /**
     * Revierte las migraciones eliminando las tablas creadas.
     *
     * Este método define las acciones a realizar cuando se ejecuta `php artisan migrate:rollback`.
     * Elimina las tablas de caché y de bloqueos de caché.
     * 
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'cache' si existe
        Schema::dropIfExists('cache');

        // Elimina la tabla 'cache_locks' si existe
        Schema::dropIfExists('cache_locks');
    }
};
