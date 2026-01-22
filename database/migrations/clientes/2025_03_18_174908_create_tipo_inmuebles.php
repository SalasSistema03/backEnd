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
        if (!Schema::connection($this->connection)->hasTable('tipo_inmuebles')) {
            Schema::connection($this->connection)->create('tipo_inmuebles', function (Blueprint $table) {
                $table->id('id_tipo_inmbueble'); // BIGINT UNSIGNED AUTO_INCREMENT
                $table->text('inmueble')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_inmuebles');
    }
};
