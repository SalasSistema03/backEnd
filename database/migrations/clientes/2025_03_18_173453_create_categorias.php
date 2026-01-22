<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = "mysql5";
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection($this->connection)->hasTable('categorias')) {
            Schema::connection($this->connection)->create('categorias', function (Blueprint $table) {
                $table->id('id_categoria'); // BIGINT UNSIGNED AUTO_INCREMENT
                $table->text('categoria')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_clientes');
    }
};
