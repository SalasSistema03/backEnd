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
        Schema::connection($this->connection)->create('historial_cod_ofrecimiento', function (Blueprint $table) {
            $table->id(); // equivale a bigint UNSIGNED AUTO_INCREMENT
            $table->integer('codigo_ofrecimiento');
            $table->text('mensaje');
            $table->datetime('fecha_hora');
            $table->text('last_modified_by');
            $table->unsignedInteger('id_criterio_venta');
            
            $table->foreign('id_criterio_venta')->references('id_criterio_venta')->on('criterio_busqueda_venta')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_cod_ofrecimiento');
    }
};
