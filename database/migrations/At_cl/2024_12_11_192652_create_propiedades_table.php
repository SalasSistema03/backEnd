<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'propiedades' en la base de datos.
 * 
 * Esta clase gestiona la creación y eliminación de la tabla 'propiedades',
 * que incluye las relaciones con otras entidades del sistema inmobiliario
 * como barrios, calles, estados, etc.
 * 
 * @package Database\Migrations
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla de propiedades.
     * 
     * Crea la estructura de la tabla 'propiedades' que incluye:
     * - Un identificador primario
     * - Claves foráneas para las entidades relacionadas (por ejemplo, barrio, calle, etc.)
     * - Campos de timestamp para registrar la creación y actualización de cada propiedad.
     * 
     * @return void
     */
    public function up(): void
    {
        // Crear la tabla 'propiedades' con la estructura y relaciones definidas
        Schema::create('propiedades', function (Blueprint $table) {
            // Clave primaria autoincrementable
            $table->id();

            // Ubicación de la propiedad
            $table->unsignedBigInteger('id_provincia')->nullable();
            $table->unsignedBigInteger('id_localidad')->nullable();
            $table->unsignedBigInteger('id_barrio')->nullable();
            $table->unsignedBigInteger('id_zona')->nullable();
            $table->unsignedBigInteger('id_calle')->nullable();
            $table->integer('numero_calle')->nullable();
            $table->integer('piso')->nullable();
            $table->integer('departamento')->nullable();

            // Características generales de la propiedad
            $table->unsignedBigInteger('id_inmueble')->nullable(); // Tipo de inmueble
            $table->integer('cantidad_dormitorios')->nullable();
            $table->text('banios')->nullable();
            $table->text('mLote')->nullable();
            $table->text('mCubiertos')->nullable();
            $table->text('cochera')->nullable();
            $table->text('numero_cochera')->nullable();
            $table->text('asfalto')->nullable();
            $table->text('gas')->nullable();
            $table->text('cloaca')->nullable();
            $table->text('agua')->nullable();
            $table->text('ph')->nullable();

            // Información sobre la venta y alquiler
            $table->integer('cod_alquiler')->nullable()->unique();
            $table->unsignedBigInteger('id_estado_alquiler')->nullable();
            $table->text('autorizacion_alquiler')->nullable();
            $table->text('exclusividad_alquiler')->nullable();
            $table->date('fecha_autorizacion_alquiler')->nullable();
            $table->date('vencimientoDeContrato')->nullable();
            $table->integer('cod_venta')->nullable()->unique();
            $table->unsignedBigInteger('id_estado_venta')->nullable();
            $table->text('autorizacion_venta')->nullable();
            $table->text('exclusividad_venta')->nullable();
            $table->text('condicionado_venta')->nullable();
            $table->text('comparte_venta')->nullable();
            $table->text('clausula_de_venta')->nullable();
            $table->text('tiempo_clausula')->nullable(); 
            $table->date('fecha_autorizacion_venta')->nullable();
            $table->date('venta_fecha_alta')->nullable();
            $table->date('alquiler_fecha_alta')->nullable();
            $table->date('fecha_publicacion_ig')->nullable();

            // Estado general y otros detalles
            $table->unsignedBigInteger('id_estado_general')->nullable();
            $table->integer('folio')->nullable();
            $table->integer('empresa')->nullable();
            $table->integer('llave')->nullable();
            $table->text('comentario_llave')->nullable();
            $table->text('cartel')->default(false)->nullable();
            $table->text('comentario_cartel')->nullable();
            $table->text('descipcion_propiedad')->nullable();

            // Auditoría y timestamps
            $table->unsignedBigInteger('last_modified_by')->nullable();
            $table->timestamps();

            // Definición de claves foráneas
            $table->foreign('id_provincia')->references('id')->on('provincia')->nullOnDelete();
            $table->foreign('id_localidad')->references('id')->on('localidad')->nullOnDelete();
            $table->foreign('id_barrio')->references('id')->on('barrio')->nullOnDelete();
            $table->foreign('id_zona')->references('id')->on('zona')->nullOnDelete();
            $table->foreign('id_calle')->references('id')->on('calle')->nullOnDelete();
            $table->foreign('id_inmueble')->references('id')->on('tipo_inmueble')->nullOnDelete();
            $table->foreign('id_estado_alquiler')->references('id')->on('estado_alquileres')->nullOnDelete();
            $table->foreign('id_estado_venta')->references('id')->on('estado_ventas')->nullOnDelete();
           /*  $table->foreign('id_precio')->references('id')->on('precio')->nullOnDelete(); */
           /*  $table->foreign('id_tasacion')->references('id')->on('tasacion')->nullOnDelete(); */
            $table->foreign('id_estado_general')->references('id')->on('estado_general')->nullOnDelete();
          /*   $table->foreign('last_modified_by')->references('id')->on('usuarios')->nullOnDelete(); */
        
        });
    }

    /**
     * Revierte la migración, eliminando la tabla 'propiedades'.
     * 
     * Método de rollback que elimina la tabla de propiedades creada en el método `up()`.
     * Utiliza `dropIfExists` para evitar errores si la tabla no existe.
     * 
     * @return void
     */
    public function down(): void
    {
        // Elimina la tabla 'propiedades' si existe
        Schema::dropIfExists('propiedades');
    }
};
