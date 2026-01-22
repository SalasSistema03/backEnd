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
        Schema::connection($this->connection)->create('historial_cod_muestra', function (Blueprint $table) {
            $table->id(); // equivale a bigint UNSIGNED AUTO_INCREMENT
            $table->integer('codigo_muestra');
            $table->text('mensaje');
            $table->datetime('fecha_hora');
            $table->text('last_modified_by');
            $table->unsignedBigInteger('id_criterio_venta');
            
            
            $table->foreign('id_criterio_venta')->references('id')->on('historial_criterios_conversacion')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_cod_muestra');
    }
};
