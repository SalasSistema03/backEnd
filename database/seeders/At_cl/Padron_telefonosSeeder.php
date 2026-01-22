<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Padron_telefonos;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Padron_telefonosSeeder extends Seeder
{
    /**
    *Este método genera e inserta 10 registros aleatorios en la tabla "Padron_telefonos"
     * utilizando el factory del modelo Padron_telefonos.
     */
    public function run(): void
    {
        // Genera e inserta 10 registros aleatorios en la tabla "Padron_telefonos"
        Padron_telefonos::factory(10)->create();
    }
}
