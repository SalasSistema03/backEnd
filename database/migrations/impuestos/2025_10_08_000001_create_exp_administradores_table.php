<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    public function up(): void
    {
        Schema::connection($this->connection)->create('exp_administradores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('telefono', 50)->nullable();
            $table->string('mail', 150)->nullable();
            $table->string('recepcion', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('exp_administradores');
    }
};
