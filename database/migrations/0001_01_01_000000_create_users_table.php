<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para crear las tablas de la base de datos.
     *
     * Este método define las tablas necesarias para gestionar usuarios, tokens de restablecimiento de contraseñas y sesiones
     * de usuario. Se ejecuta cuando se corre la migración con `php artisan migrate`.
     * 
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'users' para almacenar información de los usuarios
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Crea una columna auto incremental para el ID del usuario
            $table->string('name'); // Crea una columna para el nombre del usuario
            $table->string('email')->unique(); // Crea una columna para el correo electrónico y lo marca como único
            $table->timestamp('email_verified_at')->nullable(); // Marca la fecha de verificación del correo como nullable
            $table->string('password'); // Crea una columna para almacenar la contraseña del usuario
            $table->rememberToken(); // Crea una columna para almacenar el token de "remember me" (recordarme)
            $table->timestamps(); // Crea las columnas `created_at` y `updated_at` para el control de fechas
        });

        // Crea la tabla 'password_reset_tokens' para almacenar tokens de restablecimiento de contraseñas
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Usa el correo como clave primaria, asociándolo al usuario
            $table->string('token'); // Crea una columna para almacenar el token de restablecimiento
            $table->timestamp('created_at')->nullable(); // Marca la fecha de creación del token como nullable
        });

        // Crea la tabla 'sessions' para gestionar las sesiones activas de los usuarios
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Usa el ID de la sesión como clave primaria
            $table->foreignId('user_id')->nullable()->index(); // Crea una relación con la tabla de usuarios, permitiendo valores nulos
            $table->string('ip_address', 45)->nullable(); // Almacena la dirección IP de la sesión, permitiendo valores nulos
            $table->text('user_agent')->nullable(); // Almacena el user agent (navegador y sistema operativo) de la sesión, permitiendo valores nulos
            $table->longText('payload'); // Almacena información adicional de la sesión en formato largo
            $table->integer('last_activity')->index(); // Almacena la marca de tiempo de la última actividad y la indexa para optimización de consultas
        });
    }

    /**
     * Revierte las migraciones eliminando las tablas creadas.
     *
     * Este método define las acciones a realizar cuando se ejecuta `php artisan migrate:rollback`. 
     * Elimina las tablas de usuarios, tokens de restablecimiento de contraseñas y sesiones de usuario.
     * 
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'users' si existe
        Schema::dropIfExists('users');

        // Elimina la tabla 'password_reset_tokens' si existe
        Schema::dropIfExists('password_reset_tokens');

        // Elimina la tabla 'sessions' si existe
        Schema::dropIfExists('sessions');
    }
};
