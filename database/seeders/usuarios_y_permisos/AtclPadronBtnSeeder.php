<?php

namespace Database\Seeders\usuarios_y_permisos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtclPadronBtnSeeder extends Seeder
{
    
    public function run()
    {
        DB::connection('mysql4')->table('atcl_padron_btn')->insert([
            ['btn_nombre' => 'editarPadron', 'vista_id' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}