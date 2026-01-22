<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historial_fechas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('propiedad_id'); // Clave foránea a la tabla 'propiedades'
            $table->date('fecha_de_alta');
            $table->date('fecha_de_baja');
            $table->timestamps();
        
            // Definir la relación con la tabla 'propiedades'
            $table->foreign('propiedad_id')->references('id')->on('propiedades')->cascadeOnDelete();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_fechas');
    }
};
