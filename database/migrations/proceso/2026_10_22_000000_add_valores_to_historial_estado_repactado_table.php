<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega los valores económicos del repactado y permite texto en el plazo.
 *
 * El monto del documento y el reconocimiento se guardan al cargar el repactado
 * del período. El plazo y actualización del contrato puede contener texto, no
 * solo un número de meses.
 */
return new class extends Migration
{
    protected $connection = 'mysql10';

    public function up(): void
    {
        Schema::connection($this->connection)->table('historial_estado_repactado', function (Blueprint $table) {
            $table->decimal('monto_documento', 12, 2)->nullable()->default(0)->after('monto_alquiler_viejo');
            $table->decimal('reconocimiento', 12, 2)->nullable()->default(0)->after('monto_documento');
            $table->string('plazo_y_actualizacion_contrato', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('historial_estado_repactado', function (Blueprint $table) {
            $table->dropColumn(['monto_documento', 'reconocimiento']);
            $table->integer('plazo_y_actualizacion_contrato')->nullable()->change();
        });
    }
};
