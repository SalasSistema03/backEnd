<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql5";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('criterio_busqueda_alquiler')) {
            Schema::connection($this->connection)->create('criterio_busqueda_alquiler', function (Blueprint $table) {
                $table->bigIncrements('id_criterio'); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
                $table->unsignedBigInteger('id_cliente');
                $table->integer('id_tipo_inmueble');
                $table->unsignedBigInteger('id_categoria');
                $table->integer('cant_dormitorios')->nullable();
                $table->text('observaciones_criterio_alquiler')->nullable();
                $table->string('estado_criterio_alquiler', 15)->nullable();
                $table->string('situacion_criterio_alquiler', 15)->nullable();
                $table->dateTime('fecha_criterio_alquiler')->nullable();
                $table->integer('usuario_id');

                // Relaciones
                $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
                $table->foreign('id_tipo_inmueble')->references('id_tipo_inmbueble')->on('tipo_inmuebles');
                $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('criterio_busqueda_alquiler');
    }
};