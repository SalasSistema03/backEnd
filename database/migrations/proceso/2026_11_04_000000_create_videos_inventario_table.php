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
        Schema::connection('mysql10')->create('videos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_proceso_propiedad');
            $table->string('url');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Si necesitas FK, asumo que la tabla padre está en la misma conexión
            // $table->foreign('id_proceso_propiedad')->references('id')->on('proceso_propiedad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql10')->dropIfExists('videos_inventario');
    }
};
