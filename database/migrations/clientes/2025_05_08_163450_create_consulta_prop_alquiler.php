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
        if (!Schema::connection($this->connection)->hasTable('consulta_prop_alquiler')) {
            Schema::connection($this->connection)->create('consulta_prop_alquiler', function (Blueprint $table) {
                $table->increments('id_con_prop_alquiler');
                $table->string('tipo_consulta', 80)->nullable();
                $table->string('observ_con_alquiler', 80)->nullable();
                $table->unsignedBigInteger('id_cliente');
                $table->unsignedBigInteger('id_propiedad');

                // Índices
                $table->index('id_cliente', 'FK_consulta_prop_alquiler_cliente');
                $table->index('id_propiedad', 'FK_consulta_prop_alquiler_propiedades');

                // Claves foráneas
                $table->foreign('id_cliente')
                      ->references('id_cliente')->on('clientes')
                      ->onDelete('cascade');

                $table->foreign('id_propiedad')
                      ->references('id')->on('sistema_atcl.propiedades')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_prop_alquiler');
    }
};
