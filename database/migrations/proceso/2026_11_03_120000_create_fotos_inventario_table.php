<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fotos cargadas durante el inventario del Departamento Técnico.
 *
 * Se asocian al proceso de reserva (`id_proceso_propiedad`): las ediciones del
 * inventario crean filas nuevas en historial_estado_dpto, pero el proceso es el
 * mismo, así que las fotos no se pierden. Una reserva caída genera un proceso
 * nuevo con sus propias fotos.
 *
 * `url` es la ruta relativa servida por el alias /inventario de Apache.
 * `quien_cargo` es el id del usuario (sin clave foránea: usuarios vive en mysql4).
 */
return new class extends Migration
{
    protected $connection = 'mysql10';

    public function up(): void
    {
        Schema::connection($this->connection)->create('fotos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso_propiedad');
            $table->unsignedBigInteger('propiedad_id');
            $table->string('url', 500);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('quien_cargo')->nullable();
            $table->timestamps();

            $table->index('id_proceso_propiedad', 'idx_fi_proceso');
            $table->index('propiedad_id', 'idx_fi_propiedad');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('fotos_inventario');
    }
};
