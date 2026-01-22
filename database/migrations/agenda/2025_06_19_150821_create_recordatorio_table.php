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
        Schema::create('recordatorio', function (Blueprint $table) {
            $table->id();
            $table->text('descripcion');
            $table->unsignedBigInteger('agenda_id')->nullable();
            $table->date('fecha_inicio');
            $table->integer('intervalo');
            $table->date('fecha_actualizacion');
            $table->date('fecha_fin');
            $table->unsignedBigInteger('usuario_carga');
            $table->unsignedBigInteger('usuario_finaliza')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->time('hora')->nullable();
            $table->integer('cantidad')->nullable();
            $table->integer('repetir')->nullable();

            $table->foreign('agenda_id')->references('id')->on('agenda')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recordatorio');
    }
};
