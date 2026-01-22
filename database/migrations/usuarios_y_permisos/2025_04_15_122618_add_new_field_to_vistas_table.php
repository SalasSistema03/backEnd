<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql4";
    public function up(): void
    {
        Schema::connection($this->connection)->table('vistas', function (Blueprint $table) {
            $table->boolean('es_nav')->default(false);
            $table->string('ruta')->nullable(); // Agregar el nuevo campo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vistas', function (Blueprint $table) {
            //
        });
    }
};
