<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\estado_general;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Estado_generalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estado_general = new estado_general();
        $estado_general->estado_general = 'EXCELENTE';
        $estado_general->save();  

        $estado_general = new estado_general();
        $estado_general->estado_general = 'MUY BUENO';
        $estado_general->save();  
        
       $estado_general = new estado_general();
        $estado_general->estado_general = 'BUENO';
        $estado_general->save();  

        $estado_general = new estado_general();
        $estado_general->estado_general = 'REGULAR';
        $estado_general->save();  

        $estado_general = new estado_general();
        $estado_general->estado_general = 'MALO';
        $estado_general->save();  

        
    }
}
