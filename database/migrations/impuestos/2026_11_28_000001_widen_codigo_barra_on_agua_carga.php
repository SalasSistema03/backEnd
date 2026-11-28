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
            Schema::connection($this->connection)->hasTable('agua_carga') &&
            Schema::connection($this->connection)->hasColumn('agua_carga', 'codigo_barra')
        ) {
            Schema::connection($this->connection)->table('agua_carga', function (Blueprint $table) {
                $table->string('codigo_barra', 60)->change();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::connection($this->connection)->hasTable('agua_carga') &&
            Schema::connection($this->connection)->hasColumn('agua_carga', 'codigo_barra')
        ) {
            Schema::connection($this->connection)->table('agua_carga', function (Blueprint $table) {
                $table->string('codigo_barra', 45)->change();
            });
        }
    }
};
