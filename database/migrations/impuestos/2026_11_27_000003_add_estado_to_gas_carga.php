<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    public function up(): void
    {
        if (
            Schema::connection($this->connection)->hasTable('gas_carga') &&
            !Schema::connection($this->connection)->hasColumn('gas_carga', 'estado')
        ) {
            Schema::connection($this->connection)->table('gas_carga', function (Blueprint $table) {
                $table->string('estado', 20)->nullable()->after('num_broche');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::connection($this->connection)->hasTable('gas_carga') &&
            Schema::connection($this->connection)->hasColumn('gas_carga', 'estado')
        ) {
            Schema::connection($this->connection)->table('gas_carga', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
        }
    }
};
