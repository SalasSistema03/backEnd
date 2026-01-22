<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Observaciones_propiedadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los IDs de propiedades existentes
        $propiedadIds = DB::table('propiedades')->pluck('id')->toArray();

        if (empty($propiedadIds)) {
            echo "No hay propiedades en la base de datos.\n";
            return;
        }

        // Generar 10 observaciones aleatorias
        $observaciones = [];
        for ($i = 0; $i < 10; $i++) {
            $observaciones[] = [
                'propiedad_id' => $propiedadIds[array_rand($propiedadIds)], // ID aleatorio de propiedad
                'notes' => 'Observación de prueba ' . ($i + 1), // Texto de observación
                'tipo_ofera' => rand(0, 1) ? 'V' : 'A', // Tipo de oferta aleatorio ('V' o 'A')
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar las observaciones en la base de datos
        DB::table('observaciones_propiedades')->insert($observaciones);

        echo "Se generaron 10 registros en la tabla 'observaciones_propiedades'.\n";
    }
}
