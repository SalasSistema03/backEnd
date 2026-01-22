<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql3";
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('valor_gasto_administrativo', function(Blueprint $table){
            $table->id('id_valor_gasto_administrativo');
            $table->string('tipo');
            $table->integer('valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('valor_gasto_administrativo');
    }
};
