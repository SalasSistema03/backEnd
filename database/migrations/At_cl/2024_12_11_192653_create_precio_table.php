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
        Schema::create('precio', function (Blueprint $table) {
            $table->id();
            $table->text('moneda')->nullable(); 
            $table->float('moneda_alquiler_dolar')->nullable();
            $table->float('moneda_alquiler_pesos')->nullable();
            $table->date('alquiler_fecha_alta')->nullable();
            $table->date('alquiler_fecha_baja')->nullable();
            $table->float('moneda_venta_pesos')->nullable();
            $table->float('moneda_venta_dolar')->nullable();
            $table->date('venta_fecha_alta')->nullable();
            $table->date('venta_fecha_baja')->nullable();
            $table->foreignId('propiedad_id') // Clave foránea relacionada con 'padron'
                  ->constrained('propiedades') // Establece la relación con la tabla 'padron'
                  ->cascadeOnDelete();
            //$table->text('comentario_venta');
            //$table->text('comentario_alquiler');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio');
    }
};
