<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\At_cl\Tipo_inmueble;


class InmuebleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'CASA';
        $inmueble->save();

        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'DEPARTAMENTO';
        $inmueble->save();

        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'GALPON';
        $inmueble->save();

        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'TERRRENO';
        $inmueble->save();

        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'COCHERA';
        $inmueble->save();


        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'OFICINA';
        $inmueble->save();

        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'CAMPO';
        $inmueble->save();

        $inmueble = new Tipo_inmueble();
        $inmueble->inmueble = 'LOCAL COMERCIAL';
        $inmueble->save();
    }
}
