<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql6';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sector_id'); 
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();


            // Solo puedes agregar la foreign key del sector porque está en la misma BD
            $table->foreign('sector_id')->references('id')->on('sectores')->onDelete('cascade');
            // NO agregues foreign para usuario_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda');
    }
};
