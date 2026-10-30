<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea el historial de conversación de un repactado.
 *
 * Cada fila es un mensaje escrito por una persona del sistema, que indica si
 * lo dijo el propietario o el inquilino. La fecha y hora es la del momento en
 * que se guarda el mensaje.
 *
 * El repactado se referencia por `id_historial_estado_repactado` como referencia
 * lógica (sin clave foránea), igual que el resto del módulo.
 */
return new class extends Migration
{
    protected $connection = 'mysql10';

    public function up(): void
    {
        Schema::connection($this->connection)->create('historial_conversacion_repactado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_historial_estado_repactado');
            // Nombre explícito: el generado por defecto supera el límite de 64 caracteres de MySQL.
            $table->index('id_historial_estado_repactado', 'idx_hcr_historial_estado');
            $table->string('tipo_persona', 20);
            $table->dateTime('fecha_hora');
            $table->text('mensaje');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('historial_conversacion_repactado');
    }
};
