<?php

namespace Database\Seeders\usuarios_y_permisos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Atcl1VistaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql4')->table('atcl_1_vista')->insert([
            // Sección Propiedad
            ['vista_nombre' => 'propiedadBuscar', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'propiedadCargar', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'propiedadpadronCargar', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'propiedad', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'propiedadEditar', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'propiedadEditarFotos', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'propiedadEditarDocumentos', 'Seccion' => 'Propiedad', 'menu_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            // Sección Padron
            ['vista_nombre' => 'padronBuscar', 'Seccion' => 'Padron', 'menu_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'padronCargar', 'Seccion' => 'Padron', 'menu_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'padronEditar', 'Seccion' => 'Padron', 'menu_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['vista_nombre' => 'padron', 'Seccion' => 'Padron', 'menu_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}