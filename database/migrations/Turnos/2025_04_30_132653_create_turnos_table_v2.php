<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    protected $connection = 'mysql7';
    
    public function up(): void
    {
        Schema::connection($this->connection)->create('turnos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_identificador');
            $table->enum('tipo_identificador', ['DNI', 'Folio']);
            $table->string('sector');
           $table->unsignedBigInteger('tomo_usuario_id')->nullable(20);
            $table->foreignId('usuario_id')->nullable();
            $table->timestamp('fecha_carga')->useCurrent();
            $table->timestamp('fecha_llamado')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('turnos');
    }
};
