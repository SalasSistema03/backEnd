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
        
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_id');
            /* $table->string('titulo'); */
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('creado_por');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('propiedad_id')->nullable();
            $table->date('fecha');
            $table->string('calle')->nullable();
            $table->text('devoluciones')->nullable();
         
            $table->timestamps();



            $table->foreign('agenda_id')->references('id')->on('agenda')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
