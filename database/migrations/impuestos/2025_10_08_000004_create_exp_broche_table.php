<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    public function up(): void
    {
        Schema::connection($this->connection)->create('exp_broche', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_departamento')->nullable();
            $table->decimal('importe', 12, 2)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('periodo_anio')->nullable();
            $table->integer('periodo_mes')->nullable();
            $table->unsignedBigInteger('num_broche')->nullable(); // referencia a administradores
            $table->date('comienza')->nullable();
            $table->date('rescicion')->nullable();
            $table->decimal('exp_comunes', 12, 2)->nullable();
            $table->decimal('exp_extraordinarias', 12, 2)->nullable();

            $table->foreign('id_departamento')
                ->references('id')
                ->on('exp_departamentos')
                ->onDelete('set null');

            $table->foreign('num_broche')
                ->references('id')
                ->on('exp_administradores')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('exp_broche');
    }
};
