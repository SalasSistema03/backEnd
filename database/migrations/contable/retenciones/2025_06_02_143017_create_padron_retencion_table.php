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
        Schema::connection($this->connection)->create('padron_retencion', function(Blueprint $table){
            $table->id('id_padron_retencion');
            //$table->unsignedBigInteger('cuit_retencion');
            $table->string('razon_social_retencion');
            $table->string('domicilio_retencion');
            $table->string('localidad_retencion');
            $table->unsignedBigInteger('id_provincia_retencion');
            $table->integer('codigo_postal_retencion');
            $table->unsignedBigInteger('last_modified_by')->nullable();


            $table->foreign('id_provincia_retencion')->references('id_provincia_retencion')->on('provincia_retencion')->onDelete('cascade');
        });
    }

   
   
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('padron_retencion');
    }
};
