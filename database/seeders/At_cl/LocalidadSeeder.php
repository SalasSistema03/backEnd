<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Localidad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalidadSeeder extends Seeder
{
    /**
    *Este método genera e inserta 10 registros aleatorios en la tabla "Localidad"
     * utilizando el factory del modelo Localidad.
     */
    public function run(): void
    {
        $localidades = [
            'Santa Fe',
            'Rosario',
            'Santo Tomé',
            'Rafaela',
            'Venado Tuerto',
            'Reconquista',
            'Gálvez',
            'Esperanza',
            'Casilda',
            'San Lorenzo',
            'Villa Gobernador Gálvez',
            'Villa Constitución',
            'San Justo',
            'San Javier',
            'Ceres',
            'Sunchales',
            'San Jorge',
            'Rufino',
            'Funes',
            'Cañada de Gómez',
            'Gálvez',
            'San Cristóbal',
            'Pérez',
            'Firmat',
            'Las Toscas',
            'Las Rosas',
            'Vera',
            'Arroyo Seco',
            'Villa Cañás',
            'El Trébol',
            'Avellaneda',
            'Totoras',
            'San Carlos Centro',
            'Carcarañá',
            'Granadero Baigorria',
            'Coronda',
            'Roldán',
            'Armstrong',
            'San Genaro',
            'Capitán Bermúdez'
        ];

    // Insertar en la base de datos
    foreach ($localidades as $localidad) {
        DB::table('localidad')->insert([
            'name' => $localidad,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    }
}
