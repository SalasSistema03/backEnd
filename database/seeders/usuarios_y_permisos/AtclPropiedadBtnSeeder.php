<?php

namespace Database\Seeders\usuarios_y_permisos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtclPropiedadBtnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql4')->table('atcl_propiedad_btn')->insert([
            ['btn_nombre' => 'propietario', 'vista_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['btn_nombre' => 'informacion_venta', 'vista_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['btn_nombre' => 'informacion_alquiler', 'vista_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['btn_nombre' => 'modificar', 'vista_id' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}