<?php

namespace Database\Seeders\usuarios_y_permisos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NavSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('mysql4')->table('nav')->insert([
            ['menu' => 'Atencion a Cliente', 'created_at' => now(), 'updated_at' => now()],
            ['menu' => 'Contable', 'created_at' => now(), 'updated_at' => now()],
            ['menu' => 'Agenda', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}