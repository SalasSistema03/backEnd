<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Barrio;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarrioSeeder extends Seeder
{
    /**
     *Este método genera e inserta 10 registros aleatorios en la tabla "barrio"
     * utilizando el factory del modelo Barrio.
     */
    public function run(): void
    {
       $barrios = [ 
        'COLASTINE',
        'RINCON',
        'ARROYO LEYES',
        'CENTENARIO',
        'SUR',
        'SAN LORENZO',
        'CONSTITUYENTE',
        'RECOLETA',
        'MARIANO COMAS',
        'MARIA SELVA',
        'SARGENTO CABRAL',
        'CANDIOTI',
        'GUADALUPE',
        'GUADALUPE OESTE',
        '7 JEFES',
        'DON BOSCO',
        'ZONA NORTE'
    ];

        // Insertar en la base de datos
        foreach ($barrios as $barrio) {
            DB::table('barrio')->insert([
                'name' => $barrio,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
     }
    }
}
