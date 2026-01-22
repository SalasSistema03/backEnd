<?php

namespace Database\Seeders\At_cl;

use App\Models\At_cl\Estado_venta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Estado_ventaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estado_venta = new Estado_venta();
        $estado_venta->name = 'EN VENTA';
        $estado_venta->save(); 

        $estado_venta = new Estado_venta();
        $estado_venta->name = 'EN VENTA COMPARTIDA';
        $estado_venta->save(); 

       /*  $estado_venta = new Estado_venta();
        $estado_venta->name = 'MOSCÚ';
        $estado_venta->save(); */

        $estado_venta = new Estado_venta();
        $estado_venta->name = 'VENDIDA';
        $estado_venta->save(); 

        $estado_venta = new Estado_venta();
        $estado_venta->name = 'BAJA TEMPORAL';
        $estado_venta->save(); 

        $estado_venta = new Estado_venta();
        $estado_venta->name = 'RESERVADO';
        $estado_venta->save(); 

        $estado_venta = new Estado_venta();
        $estado_venta->name = 'PENDIENTE';
        $estado_venta->save(); 

    }
}
