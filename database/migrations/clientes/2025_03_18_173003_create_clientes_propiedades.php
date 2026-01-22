<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql5"; // Cambiado a $connection
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('clientes_propiedades')) {
            Schema::connection($this->connection)->create('clientes_propiedades', function (Blueprint $table) {
                $table->id('id_cliente_propiedad'); // BIGINT UNSIGNED AUTO_INCREMENT
                $table->text('observaciones')->nullable();
                $table->dateTime('fecha_carga')->nullable();

                // Claves foráneas
                $table->unsignedBigInteger('id_cliente');
                $table->unsignedBigInteger('id_propiedad')->nullable();
                $table->unsignedBigInteger('id_asesor')->nullable();

                // Definir relaciones
                $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->onDelete('cascade');
                $table->foreign('id_propiedad')->references('id')->on('propiedades')->onDelete('set null');
                $table->foreign('id_asesor')->references('id')->on('asesores')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('clientes_propiedades');
    }
};
