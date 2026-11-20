<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    private array $tables = [
        'tgi_padron',
        'agua_padron',
        'api_padron',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (
                Schema::connection($this->connection)->hasTable($tableName)
                && !Schema::connection($this->connection)->hasColumn($tableName, 'id_contrato_cabecera')
            ) {
                Schema::connection($this->connection)->table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('id_contrato_cabecera')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (
                Schema::connection($this->connection)->hasTable($tableName)
                && Schema::connection($this->connection)->hasColumn($tableName, 'id_contrato_cabecera')
            ) {
                Schema::connection($this->connection)->table($tableName, function (Blueprint $table) {
                    $table->dropColumn('id_contrato_cabecera');
                });
            }
        }
    }
};
