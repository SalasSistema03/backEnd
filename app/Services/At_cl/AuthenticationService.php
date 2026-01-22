<?php
// app/Services/AuthenticationService.php

namespace App\Services\At_cl;


use App\Models\At_cl\Usuario;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthenticationService
{
    /**
     * Autenticar un usuario
     * 
     * @param array $usuarios
     * @return Usuario
     * @throws ValidationException
     */
    public function authenticate(array $credenciales): Usuario
    {
        $user = Usuario::where('username', $credenciales['usuario'])->first();
       
        if (!$user) {
            throw ValidationException::withMessages([
                'usuario' => ['Usuario o contraseña incorrectos.'],
            ]);
        }

        if ($user->password !== $credenciales['password']) {
            throw ValidationException::withMessages([
                'password' => ['Usuario o contraseña incorrectos.'],
            ]);
        }

        $this->updateLastActivity($user);
        return $user;
    }

    /**
     * Actualizar último acceso
     */
    private function updateLastActivity(Usuario $user): void
    {
        $user->updated_at = Carbon::now();
        $user->save();
    }

    /**
     * Crear sesión de usuario
     */
    public function createSession(Usuario $user): void
    {
       
        session::put([
            'usuario' => $user,
            'usuario_id' => $user->id,
            'usuario_nombre' => $user->name, 
            'usuario_interno' => $user->username,
            'admin' => $user->admin,
            'last_activity' => Carbon::now(),  
        ]);
       
    }
}