<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\At_cl\estado_alquiler;
class Estado_alquileresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'EN ALQUILER';
        $estado_alquiler->save(); 

        $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'EN ALQUILER COMPARTIDO';
        $estado_alquiler->save(); 

         /* $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'EN ALQUILER RETIRADO';
        $estado_alquiler->save();  */

        $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'ALQUILADA';
        $estado_alquiler->save(); 
        
        $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'BAJA TEMPORAL';
        $estado_alquiler->save(); 

        $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'RESERVADO';
        $estado_alquiler->save();

        $estado_alquiler = new estado_alquiler();
        $estado_alquiler->name = 'PENDIENTE';
        $estado_alquiler->save(); 
        
    }
}
