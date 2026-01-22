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
        Schema::create('observaciones_propiedades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('propiedad_id');
            $table->string('notes')->nullable();
            $table->text('tipo_ofera');
            $table->timestamps();

            $table->unsignedBigInteger('last_modified_by')->nullable();
           /*  $table->foreign('last_modified_by')->references('id')->on('usuarios')->nullOnDelete(); */
            $table->foreign('propiedad_id')->references('id')->on('propiedades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observaciones_propiedades');
    }
};
