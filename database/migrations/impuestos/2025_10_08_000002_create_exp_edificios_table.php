<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    public function up(): void
    {
        Schema::connection($this->connection)->create('exp_edificios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('direccion', 200);
            $table->unsignedBigInteger('id_exp_administradores')->nullable();

            $table->foreign('id_exp_administradores')
                ->references('id')
                ->on('exp_administradores')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('exp_edificios');
    }
};
