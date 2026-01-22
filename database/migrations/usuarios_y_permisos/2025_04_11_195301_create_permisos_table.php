<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql4";
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nav_id')->nullable(); // Relación con la tabla vistas
            $table->unsignedBigInteger('vista_id')->nullable(); // Relación con la tabla vistas
            $table->unsignedBigInteger('boton_id')->nullable(); // Relación con la tabla botones
            $table->unsignedBigInteger('usuario_id'); // Relación con la tabla usuarios
            $table->timestamps();

            // Claves foráneas
            $table->foreign('nav_id')->references('id')->on('nav')->onDelete('cascade');
            $table->foreign('vista_id')->references('id')->on('vistas')->onDelete('cascade');
            $table->foreign('boton_id')->references('id')->on('botones')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};