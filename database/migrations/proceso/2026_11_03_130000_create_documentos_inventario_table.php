<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentos Word cargados durante el inventario del Departamento Técnico.
 *
 * Hay un documento por proceso de reserva: la regla se aplica en
 * DocumentoInventarioService (al subir uno nuevo se reemplaza el anterior).
 * El nombre original no se guarda: se obtiene de la URL.
 */
return new class extends Migration
{
    protected $connection = 'mysql10';

    public function up(): void
    {
        Schema::connection($this->connection)->create('documentos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso_propiedad');
            $table->unsignedBigInteger('propiedad_id');
            $table->string('url', 500);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('quien_cargo')->nullable();
            $table->timestamps();

            $table->index('id_proceso_propiedad', 'idx_di_proceso');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('documentos_inventario');
    }
};
