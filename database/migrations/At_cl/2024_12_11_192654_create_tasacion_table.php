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
        Schema::create('tasacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('propiedad_id');
            $table->string('moneda')->nullable();
            $table->integer('tasacion_pesos_venta')->nullable();
            $table->integer('tasacion_dolar_venta')->nullable();
            $table->text('comentario_tasacion')->nullable();
           /*  $table->integer('tasacion_dolar_alquiler')->nullable();
            $table->integer('tasacion_pesos_alquiler')->nullable(); */
            $table->date('fecha_tasacion')->nullable();
            $table->timestamps();

            $table->foreign('propiedad_id')->references('id')->on('propiedades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasacion');
    }
};
