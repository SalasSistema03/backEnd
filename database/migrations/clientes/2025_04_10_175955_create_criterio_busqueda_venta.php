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
        if (!Schema::connection($this->connection)->hasTable('criterio_busqueda_venta')) {
            Schema::connection($this->connection)->create('criterio_busqueda_venta', function (Blueprint $table) {
                $table->increments('id_criterio_venta'); // INT AUTO_INCREMENT PRIMARY KEY
                $table->unsignedBigInteger('id_cliente');
                $table->integer('id_tipo_inmueble')->nullable();
                $table->unsignedBigInteger('id_categoria')->nullable();
                $table->integer('cant_dormitorio')->nullable();
                $table->string('observaciones_criterio_venta', 80)->nullable();
                $table->string('estado_criterio_venta', 15);
                $table->string('situacion_criterio_venta', 15)->nullable();
                $table->bigInteger('precio_hasta')->nullable();
                $table->dateTime('fecha_criterio_venta')->nullable();
                $table->integer('usuario_id')->nullable();


                // Relaciones (puedes agregar onDelete si aplica)
                $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
                $table->foreign('id_tipo_inmueble')->references('id_tipo_inmbueble')->on('tipo_inmuebles');
                $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('criterio_busqueda_venta');
    }
};
