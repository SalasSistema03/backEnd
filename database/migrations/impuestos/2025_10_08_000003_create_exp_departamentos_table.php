<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    public function up(): void
    {
        Schema::connection($this->connection)->create('exp_departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 100)->nullable();
            $table->string('piso', 50)->nullable();
            $table->string('unidad', 50)->nullable();
            $table->string('administra', 150)->nullable();
            $table->string('propietario', 150)->nullable();
            $table->unsignedBigInteger('id_exp_edificios')->nullable();

            $table->foreign('id_exp_edificios')
                ->references('id')
                ->on('exp_edificios')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('exp_departamentos');
    }
};
