<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Padron;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PadronSeeder extends Seeder
{
    /**
    *Este método genera e inserta 10 registros aleatorios en la tabla "Padron"
     * utilizando el factory del modelo Padron.
     */
    public function run(): void
    {
        // Genera e inserta 10 registros aleatorios en la tabla "Padron"
        Padron::factory(10)->create();
    }
}
