<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql9';

    private array $largosOriginales = [
        'tgi_carga' => 10,
        'gas_carga' => 25,
        'api_carga' => 20,
        'agua_carga' => 10,
    ];

    public function up(): void
    {
        foreach (array_keys($this->largosOriginales) as $tabla) {
            if (
                Schema::connection($this->connection)->hasTable($tabla) &&
                Schema::connection($this->connection)->hasColumn($tabla, 'num_broche')
            ) {
                Schema::connection($this->connection)->table($tabla, function (Blueprint $table) {
                    $table->string('num_broche', 30)->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->largosOriginales as $tabla => $largo) {
            if (
                Schema::connection($this->connection)->hasTable($tabla) &&
                Schema::connection($this->connection)->hasColumn($tabla, 'num_broche')
            ) {
                Schema::connection($this->connection)->table($tabla, function (Blueprint $table) use ($largo) {
                    $table->string('num_broche', $largo)->nullable()->change();
                });
            }
        }
    }
};
