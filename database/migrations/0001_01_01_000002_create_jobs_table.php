<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para crear las tablas 'jobs', 'job_batches' y 'failed_jobs'.
     *
     * Este método define las tablas necesarias para gestionar las tareas (jobs), 
     * los lotes de tareas (job_batches) y los trabajos fallidos (failed_jobs) 
     * en la base de datos. Se ejecuta cuando se corre la migración con `php artisan migrate`.
     * 
     * @return void
     */
    public function up(): void
    {
        // Crea la tabla 'jobs' para almacenar la información de las tareas en la cola
        Schema::create('jobs', function (Blueprint $table) {
            $table->id(); // Columna 'id', clave primaria auto-incremental
            $table->string('queue')->index(); // Columna 'queue' para el nombre de la cola, indexada para optimizar consultas
            $table->longText('payload'); // Columna 'payload' para almacenar la carga útil del trabajo
            $table->unsignedTinyInteger('attempts'); // Columna 'attempts' para contar los intentos de ejecución
            $table->unsignedInteger('reserved_at')->nullable(); // Columna 'reserved_at' para registrar el momento en que el trabajo fue reservado
            $table->unsignedInteger('available_at'); // Columna 'available_at' para registrar el momento en que el trabajo estará disponible
            $table->unsignedInteger('created_at'); // Columna 'created_at' para registrar la fecha de creación del trabajo
        });

        // Crea la tabla 'job_batches' para almacenar los lotes de trabajos en la cola
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // Columna 'id' como clave primaria para identificar el lote
            $table->string('name'); // Columna 'name' para almacenar el nombre del lote
            $table->integer('total_jobs'); // Columna 'total_jobs' para el número total de trabajos en el lote
            $table->integer('pending_jobs'); // Columna 'pending_jobs' para el número de trabajos pendientes en el lote
            $table->integer('failed_jobs'); // Columna 'failed_jobs' para el número de trabajos fallidos en el lote
            $table->longText('failed_job_ids'); // Columna 'failed_job_ids' para almacenar los IDs de los trabajos fallidos
            $table->mediumText('options')->nullable(); // Columna 'options' para opciones adicionales del lote (puede ser nula)
            $table->integer('cancelled_at')->nullable(); // Columna 'cancelled_at' para la fecha de cancelación del lote (si aplica)
            $table->integer('created_at'); // Columna 'created_at' para registrar la fecha de creación del lote
            $table->integer('finished_at')->nullable(); // Columna 'finished_at' para la fecha de finalización del lote (si aplica)
        });

        // Crea la tabla 'failed_jobs' para almacenar los trabajos fallidos
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // Columna 'id' como clave primaria auto-incremental
            $table->string('uuid')->unique(); // Columna 'uuid' única para identificar de forma única cada trabajo fallido
            $table->text('connection'); // Columna 'connection' para almacenar la conexión utilizada para el trabajo fallido
            $table->text('queue'); // Columna 'queue' para almacenar la cola del trabajo fallido
            $table->longText('payload'); // Columna 'payload' para almacenar la carga útil del trabajo fallido
            $table->longText('exception'); // Columna 'exception' para almacenar el mensaje de la excepción generada
            $table->timestamp('failed_at')->useCurrent(); // Columna 'failed_at' para registrar la fecha y hora en que ocurrió el fallo
        });
    }

    /**
     * Revierte las migraciones eliminando las tablas creadas.
     *
     * Este método define las acciones a realizar cuando se ejecuta `php artisan migrate:rollback`.
     * Elimina las tablas de trabajos, lotes de trabajos y trabajos fallidos de la base de datos.
     * 
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'jobs' si existe
        Schema::dropIfExists('jobs');

        // Elimina la tabla 'job_batches' si existe
        Schema::dropIfExists('job_batches');

        // Elimina la tabla 'failed_jobs' si existe
        Schema::dropIfExists('failed_jobs');
    }
};
