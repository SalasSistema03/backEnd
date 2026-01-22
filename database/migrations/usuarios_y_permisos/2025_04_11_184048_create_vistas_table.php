<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql4";
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('vistas', function (Blueprint $table) {
            $table->id();
            
            $table->enum('vista_nombre', [
                'propiedadBuscar', 'propiedadCargar', 'propiedadpadronCargar', 'propiedad', 'propiedadEditar',
                'propiedadEditarFotos', 'propiedadEditarDocumentos', 'padronBuscar', 'padronCargar', 'padronEditar', 'padron',
                'sellado','listadoAlquiler'
            ]); 
            $table->enum('Seccion', ['Propiedad', 'Cliente', 'Padron','Sellado','Listado']); 
            $table->string('nombre_visual', 50);
            
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->foreign('menu_id')->references('id')->on('nav')->onDelete('cascade'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vistas');
    }
};
