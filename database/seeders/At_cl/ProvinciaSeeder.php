<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Provincia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciaSeeder extends Seeder
{
    /**
    *Este método genera e inserta 10 registros aleatorios en la tabla "Provincia"
     * utilizando el factory del modelo Provincia.
     */
    public function run(): void
    {
        $provincias = [  'Buenos Aires',
        'Catamarca',
        'Chaco',
        'Chubut',
        'Córdoba',
        'Corrientes',
        'Entre Ríos',
        'Formosa',
        'Jujuy',
        'La Pampa',
        'La Rioja',
        'Mendoza',
        'Misiones',
        'Neuquén',
        'Río Negro',
        'Salta',
        'San Juan',
        'San Luis',
        'Santa Cruz',
        'Santa Fe',
        'Santiago del Estero',
        'Tierra del Fuego',
        'Tucumán',
        'CABA',
        ];

        // Insertar en la base de datos
        foreach ($provincias as $provincia) {
            DB::table('provincia')->insert([
                'name' => $provincia,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
