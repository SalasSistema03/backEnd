<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->text('comentario_autorizacion')->nullable()->after('condicion'); // Agregar el campo después de 'tiempo_clausula'
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('comentario_autorizacion'); // Eliminar el campo en caso de rollback
        });
    }
};