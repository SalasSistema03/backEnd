<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario' => ['required', 'string', 'max:20','min:3'],
            'password' => ['required', 'string', 'max:20', 'min:3'],
        ];
    }

    public function messages()
    {
        return [
            'usuario.required' => 'El campo usuario es obligatorio.',
            'usuario.max' => 'El usuario no puede tener más de 20 caracteres.',
            'usuario.min' => 'El usuario debe tener al menos 3 caracteres.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.max' => 'La contraseña no puede tener más de 10 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 3 caracteres.',
        ];
    }
}
