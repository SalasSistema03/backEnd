<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    public function up(): void
    {
        $tablas = ['tgi_carga', 'gas_carga', 'api_carga', 'agua_carga'];

        foreach ($tablas as $tabla) {
            if (
                Schema::connection($this->connection)->hasTable($tabla) &&
                !Schema::connection($this->connection)->hasColumn($tabla, 'origen_carga')
            ) {
                Schema::connection($this->connection)->table($tabla, function (Blueprint $table) {
                    $table->string('origen_carga', 20)->nullable()->after('num_broche');
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = ['tgi_carga', 'gas_carga', 'api_carga', 'agua_carga'];

        foreach ($tablas as $tabla) {
            if (
                Schema::connection($this->connection)->hasTable($tabla) &&
                Schema::connection($this->connection)->hasColumn($tabla, 'origen_carga')
            ) {
                Schema::connection($this->connection)->table($tabla, function (Blueprint $table) {
                    $table->dropColumn('origen_carga');
                });
            }
        }
    }
};
