<?php

namespace App\Http\Controllers\At_cl;

use App\Http\Controllers\Controller;
use App\Services\At_cl\AuthenticationService;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;

class UsuariosController extends Controller
{
   
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function verificacion(LoginRequest $request)
    {
        try {
            $user = $this->authService->authenticate($request->validated());
            $this->authService->createSession($user);
            
            return redirect()->route('home');
                
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput($request->except('password'));
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Error al iniciar sesión. Por favor, intente nuevamente.'])
                ->withInput($request->except('password'));
        }
    }



    
}
