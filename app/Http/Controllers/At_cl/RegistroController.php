<?php

namespace App\Http\Controllers\At_cl;

use Illuminate\Http\Request;
use App\Models\At_cl\Usuario; // Import the Usuario model
use App\Http\Requests\RegistroRequest; // Import the RegistroRequest class
use Illuminate\Support\Facades\DB; // Import the DB facade

class RegistroController
{
 

    public function index()
    {
        return view('usuario.registrarUsuario');
    }

    public function create() {}

    /* public function store(RegistroRequest $request) */
    public function store(RegistroRequest $request)
    {
        //dd($request->all());
        DB::beginTransaction(); // Iniciar transacción

        try {
            // Insertar nuevo usuario en la base de datos secundaria (mysql4)
            DB::connection('mysql4')->table('usuarios')->insert([
                'name' => $request->input('name'),
                'username' => $request->input('nombre_interno'),
                'password' => $request->input('password'),
                'admin' => $request->has('admin') ? 1 : 0,
                'telf_interno' => $request->input('tel_interno'),
                'telf_laboral' => $request->input('telefono_personal'),
                'fecha_nac' => $request->input('fecha_nacimiento'),
                'email_interno' => $request->input('email_interno'),
                'email_externo' => $request->input('email_externo'),
            ]);

            DB::commit(); // Confirmar cambios si todo fue exitoso

            return redirect()->route('validaciones.index')->with('success', 'Usuario registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir cambios en caso de error

            return redirect()->back()->with('error', 'Ocurrió un error al registrar el usuario: ' . $e->getMessage());
        }
    }


    public function show(string $id){}


    public function edit(string $id){}

    
    public function update(Request $request, string $id){}

    
    public function destroy(string $id){}
}
