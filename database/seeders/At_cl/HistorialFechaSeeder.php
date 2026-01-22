<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Seeder;
use App\Models\At_cl\Propiedad;
use App\Models\At_cl\HistorialFechas;

class HistorialFechaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Para cada propiedad, genera entre 1 y 5 registros de historial de fechas
        Propiedad::all()->each(function ($propiedad) {
            HistorialFechas::factory()->count(rand(1, 5))->create([
                'propiedad_id' => $propiedad->id,
            ]);
        });
    }
}
