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
        Schema::connection($this->connection)->create('base_porcentual', function(Blueprint $table){
            $table->id('id_base_porcentual');
            $table->string('nombre');
            $table->float('dato');
            $table->unsignedBigInteger('last_modified_by')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_porcentual');
    }
};
