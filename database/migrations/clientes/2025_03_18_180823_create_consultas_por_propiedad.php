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
        if (!Schema::connection($this->connection)->hasTable('consulta_por_propiedad')) {
            Schema::connection($this->connection)->create('consulta_por_propiedad', function (Blueprint $table) {
                $table->id('id_consulta_propiedad'); // BIGINT UNSIGNED AUTO_INCREMENT
                $table->string('tipo_consulta')->nullable();
                $table->text('observaciones_consulta_propiedad')->nullable();
                $table->date('fecha_consulta_propiedad')->nullable();

                // Clave foránea a clientes (tabla aún no creada)
                $table->unsignedBigInteger('id_cliente');
                $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');

                $table->unsignedBigInteger('cod_alquiler');
                $table->unsignedBigInteger('cod_venta');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas_por_propiedad');
    }
};
