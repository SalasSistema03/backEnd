<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql4";
    public function up(): void
    {
        Schema::connection($this->connection)->create('botones', function(Blueprint $table){
            $table->id();
            $table->enum('btn_nombre', ['propietario','informacion_venta','informacion_alquiler','modificar','editarPadron']);
            $table->string('nombre_visual', 50);
             $table->unsignedBigInteger('vista_id')->nullable(); // Agregar atributo user_id
            $table->foreign('vista_id')->references('id')->on('vistas')->onDelete('cascade');  
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('botones');
    }
};
