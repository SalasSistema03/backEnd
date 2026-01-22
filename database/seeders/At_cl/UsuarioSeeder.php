<?php

namespace Database\Seeders\At_cl;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\At_cl\Usuario;
class UsuarioSeeder extends Seeder
{
    /**
    *Este método genera e inserta registros en la tabla "usuario"
     * utilizando el factory del modelo usuario.
     */
    public function run(): void
    {
        $usuario = new Usuario();
        $usuario->name = 'lucas';
        $usuario->password = '123';
        $usuario->save();  
        
        $usuario = new Usuario();
        $usuario->name = 'santi';
        $usuario->password = '123';
        $usuario->save(); 
        
        $usuario = new Usuario();
        $usuario->name = 'nico';
        $usuario->password = '123';
        $usuario->save(); 

    }
}
