<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql8";
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('provincia_retencion', function(Blueprint $table){
            $table->id('id_provincia_retencion');
            $table->string('nombre_provincia_retencion');
            $table->integer('numero_provincia_retencion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provincia_retencion');
    }
};
