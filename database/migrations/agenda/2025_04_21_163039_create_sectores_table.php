<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql6';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('sectores', function(Blueprint $table){
            $table->id();
            $table->enum('nombre', ['Sistemas', 'Ventas', 'Alquiler', 'Contrato', 'Contable', 'Impuestos', 'Departamento Tecnico']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectores');
    }
};
