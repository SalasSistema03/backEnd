<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql4";
    public function up(): void
    {
        Schema::connection($this->connection)->create('nav', function(Blueprint $table){
            $table->id();
            $table->enum('menu', ['Atencion a Cliente','Contable','Agenda']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav');
    }
};
