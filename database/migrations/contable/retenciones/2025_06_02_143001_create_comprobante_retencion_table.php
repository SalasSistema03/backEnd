<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql8";
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('comprobante_retencion', function(Blueprint $table){
            $table->id('id_comprobante');
            $table->date('fecha_comprobante');
            $table->integer('numero_comprobante');
            $table->string('suma_comprobante');
            $table->string('importe_comprobante');
            $table->string('cuit_retencion');
            $table->string('importe_retencion');
            $table->string('calcula_base');
            $table->date('fecha_retencion');
            $table->unsignedBigInteger('last_modified_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_retencion');
    }
};
