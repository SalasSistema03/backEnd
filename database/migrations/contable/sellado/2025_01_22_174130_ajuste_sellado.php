<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql3";
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('registro_sellado', function(Blueprint $table){
            $table->id('id_registro_sellado');
            $table->integer('cantidad_informes');
            $table->integer('cantidad_meses');
            $table->date('fecha_inicio');
            $table->string('folio');
            $table->string('gasto_administrativo');
            $table->integer('hojas');
            $table->string('informe');
            $table->string('inq_prop');
            $table->string('iva_gasto_adm');
            $table->string('monto_alquiler_comercial');
            $table->double('monto_alquiler_vivienda');
            $table->double('monto_contrato');
            $table->double('monto_documento');
            $table->string('nombre');
            $table->string('prop_alquiler');
            $table->string('prop_doc');
            $table->string('sellado');
            $table->string('tipo_contrato');
            $table->integer('total_contrato');
            $table->double('valor_informe');
            $table->date('fecha_carga');
            $table->integer('usuario_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('registro_sellado');
    }
};
