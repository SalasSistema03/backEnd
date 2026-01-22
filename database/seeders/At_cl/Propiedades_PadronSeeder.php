<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\Padron;

/**
 * Seeder para la tabla 'propiedades_padron'.
 * 
 * Este seeder inserta registros de prueba en la tabla intermedia 'propiedades_padron',
 * asociando registros de la tabla 'propiedades' con registros de la tabla 'padron'.
 * 
 * @package Database\Seeders
 */
class Propiedades_PadronSeeder extends Seeder
{
    
    /**
     * Ejecuta el seeder para insertar datos en la tabla 'propiedades_padron'.
     * 
     * Este método obtiene una lista de propiedades y personas del padrón,
     * y crea relaciones aleatorias entre ellas.
     *
     * @return void
     */
    public function run(): void
    {
        // Obtener todos los IDs de las propiedades y del padrón
        $propiedades = Propiedad::all();
        $padrones = Padron::all();

        // Verifica si existen propiedades y padrones antes de intentar asociarlos
        if ($propiedades->isEmpty() || $padrones->isEmpty()) {
            Log::warning('No hay propiedades o padrones disponibles para asociar.');
            return;
        }

        // Crear asociaciones entre propiedades y padrones
        foreach ($propiedades as $propiedad) {
            // Asociar esta propiedad con un número aleatorio de personas del padrón
            $padronesSeleccionados = $padrones->random(rand(1, 3)); // Asociamos entre 1 a 3 padrones a una propiedad
            
            foreach ($padronesSeleccionados as $padron) {
                DB::table('propiedades_padron')->insert([
                    'propiedad_id' => $propiedad->id, // ID de la propiedad
                    'padron_id' => $padron->id, // ID del padrón
                    'created_at' => now(), // Fecha de creación
                    'updated_at' => now(), // Fecha de actualización
                ]);
            }
        }

        Log::info('Se han creado las asociaciones entre propiedades y padrones.');
    }
}
