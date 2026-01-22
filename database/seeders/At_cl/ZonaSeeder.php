<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Zona;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonaSeeder extends Seeder
{
    /**
    *Este método genera e inserta 10 registros aleatorios en la tabla "Zona"
     * utilizando el factory del modelo Zona.
     */
    public function run(): void
    {
        $zonas = [  'Barranquitas',

        'Belgrano',
        
       'Candioti',
        
        'Centenario',
        
        'Centro',
        
        'Centro Sur',
        
        'Ciudadela',
        
        'Constituyentes',
        
        'Costanera Vieja',
        
        'Countries',
        
        'Don Bosco',
        
        'Escalante',
        
        'Fomento',
        
        'Guadalupe',
        
        'Guadalupe Oeste',
        
        'Guadalupe Residencial',
        
        'Los Hornos',
        
        'Mariano Comas',
        
        'Mayoraz',
        
        'Otras zonas',
        
        'Puerto',
        
        'Recoleta',
        
        'Recreo',
        
        'Rincón / Colastiné',
        
        'Roma',
        
        'Santo Tomé',
        
        'Sauce Viejo',
        
        'Sgto Cabral',
        
        'Sur',
        
        'Villa María Selva',
        
        'Zona Norte'
    ];
        // Insertar en la base de datos
        foreach ($zonas as $zona) {
            DB::table('zona')->insert([
                'name' => $zona,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
