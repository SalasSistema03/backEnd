<?php

namespace Database\Seeders;

use App\Models\usuarios_y_permisos\Atcl1Vista;
use Illuminate\Database\Seeder;
use Database\Seeders\At_cl\LocalidadSeeder;
use Database\Seeders\At_cl\ProvinciaSeeder;
use Database\Seeders\At_cl\ZonaSeeder;
use Database\Seeders\At_cl\BarrioSeeder;
use Database\Seeders\At_cl\CalleSeeder;
use Database\Seeders\At_cl\PadronSeeder;
use Database\Seeders\At_cl\Padron_telefonosSeeder;
use Database\Seeders\At_cl\Estado_generalSeeder;
use Database\Seeders\At_cl\InmuebleSeeder;
use Database\Seeders\At_cl\Estado_ventaSeeder;
use Database\Seeders\At_cl\Estado_alquileresSeeder;
use Database\Seeders\At_cl\PrecioSeeder;
use Database\Seeders\At_cl\PropiedadSeeder;
use Database\Seeders\At_cl\HistorialFechaSeeder;
use Database\Seeders\At_cl\Observaciones_propiedadesSeeder;
use Database\Seeders\At_cl\Propiedades_PadronSeeder;
use Database\Seeders\usuarios_y_permisos\AtclPadronBtnSeeder;
use Database\Seeders\usuarios_y_permisos\AtclPropiedadBtnSeeder;
use Database\Seeders\usuarios_y_permisos\NavSeeder;
use Database\Seeders\usuarios_y_permisos\Atcl1VistaSeeder;



/**
 * Clase DatabaseSeeder para poblar la base de datos con datos iniciales.
 * 
 * Esta clase gestiona la inicialización de datos en la base de datos, ejecutando
 * varios seeders que poblan diferentes tablas con información necesaria para
 * el funcionamiento de la aplicación. El orden de ejecución de los seeders es
 * importante para asegurar la integridad referencial de los datos.
 * 
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders para poblar la base de datos.
     * 
     * Método principal de la clase que llama a los seeders de manera secuencial.
     * Cada seeder tiene la responsabilidad de poblar tablas específicas con datos.
     * El orden de ejecución es importante, especialmente cuando las tablas dependen
     * de otras para mantener la integridad referencial de los datos.
     * 
     * @return void
     * 
     * @throws \Exception Si algún seeder falla durante la ejecución
     */
    public function run(): void
    {
        // Llamada a los seeders en el orden correcto para mantener la integridad de los datos
        $this->call([
            // Seeders para poblar datos maestros y catálogos
            CalleSeeder::class,           // Seeder para poblar la tabla de calles
            LocalidadSeeder::class,       // Seeder para poblar la tabla de localidades
            ProvinciaSeeder::class,       // Seeder para poblar la tabla de provincias
            ZonaSeeder::class,            // Seeder para poblar la tabla de zonas
            BarrioSeeder::class,          // Seeder para poblar la tabla de barrios

            // Seeders para poblar datos de usuarios y entidades relacionadas
            /* PadronSeeder::class,          // Seeder para poblar la tabla de padrón
            Padron_telefonosSeeder::class, */// Seeder para poblar la tabla de teléfonos del padrón

            // Seeders para poblar estados, tipos de inmuebles y otros relacionados
            Estado_generalSeeder::class,  // Seeder para poblar la tabla de estados generales
            InmuebleSeeder::class,        // Seeder para poblar la tabla de tipos de inmueble
            Estado_ventaSeeder::class,    // Seeder para poblar la tabla de estados de venta
            Estado_alquileresSeeder::class,// Seeder para poblar la tabla de estados de alquiler

            // Seeders finales para propiedades y observaciones
            /* PrecioSeeder::class, 
            PropiedadSeeder::class,       // Seeder para poblar la tabla de propiedades
            HistorialFechaSeeder::class,
            Observaciones_propiedadesSeeder::class, */ // Seeder para poblar observaciones de propiedades
            /* Propiedades_PadronSeeder::class, */

            // Seeder para poblar la relación entre propiedades y padrón
            /* NavSeeder::class, // Seeder para poblar la vista de propiedades
            Atcl1VistaSeeder::class, // Seeder para poblar la vista de propiedades
            AtclPadronBtnSeeder::class, // Seeder para poblar la vista de propiedades
            AtclPropiedadBtnSeeder::class, */ // Seeder para poblar la vista de propiedades
          
           
         
        ]);
    }
}
