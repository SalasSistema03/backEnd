<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql5";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('clientes')) {
            Schema::connection($this->connection)->create('clientes', function (Blueprint $table) {
                $table->id('id_cliente');
                $table->string('nombre'); // Corrige el nombre de la columna
                $table->string('telefono')->nullable(); // Permite que sea opcional
                $table->text('observaciones')->nullable(); // Permite comentarios largos
                $table->string('ingreso')->nullable(); // Permite comentarios largos
                $table->unsignedBigInteger('id_asesor')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes_propiedades', function (Blueprint $table) {
            $table->dropForeign(['id_cliente']);
        });
    
        Schema::dropIfExists('clientes');
    }
};
