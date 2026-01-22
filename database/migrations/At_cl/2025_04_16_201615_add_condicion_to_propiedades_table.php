<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->text('condicion')->nullable()->after('tiempo_clausula'); // Agregar el campo después de 'tiempo_clausula'
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('condicion'); // Eliminar el campo en caso de rollback
        });
    }
};