<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       

        return [
            'name' => 'required|string|min:3|max:100',
            'nombre_interno' => 'required|alpha_dash|min:3|max:50|unique:mysql4.usuarios,username',
            'password' => 'required|string|min:3|max:255',

            // Teléfono interno: exactamente 3 dígitos, único y distinto al laboral
            'tel_interno' => ['required', 'regex:/^\d{3}$/', 'unique:mysql4.usuarios,telf_interno', 'different:telefono_personal'],

            // Teléfono laboral: número “normal” (permite +, espacios, guiones y paréntesis), 7-20 caracteres, único y distinto al interno
            'telefono_personal' => ['regex:/^[0-9+\-()\s]{7,20}$/', 'unique:mysql4.usuarios,telf_laboral', 'different:telefono_interno'],

            // Emails estándar y únicos
            'email_interno' => 'required|email:rfc,dns|unique:mysql4.usuarios,email_interno',
            'email_externo' => 'nullable|email:rfc,dns|unique:mysql4.usuarios,email_externo',

            // Fecha de nacimiento (si se usa), mínimo 18 años
            'fecha_nacimiento' => 'date|before:today',

            'admin' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no puede tener más de :max caracteres.',

            'nombre_interno.required' => 'El nombre de usuario es obligatorio.',
            'nombre_interno.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',
            'nombre_interno.min' => 'El nombre de usuario debe tener al menos :min caracteres.',
            'nombre_interno.max' => 'El nombre de usuario no puede tener más de :max caracteres.',
            'nombre_interno.unique' => 'El nombre de usuario ya está en uso.',

            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.max' => 'La contraseña no puede tener más de :max caracteres.',

            'tel_interno.required' => 'El teléfono interno es obligatorio.',
            'tel_interno.regex' => 'El teléfono interno debe tener exactamente 3 dígitos.',
            'tel_interno.unique' => 'El teléfono interno ya está registrado.',
            'tel_interno.different' => 'El teléfono interno no puede ser igual al teléfono laboral.',

            
            'telefono_personal.regex' => 'El teléfono laboral tiene un formato inválido (permite dígitos, espacios, +, - y paréntesis; 7-20 caracteres).',
            'telefono_personal.unique' => 'El teléfono laboral ya está registrado.',
            'telefono_personal.different' => 'El teléfono laboral no puede ser igual al teléfono interno.',

            'email_interno.email' => 'El email interno no tiene un formato válido.',
            'email_interno.unique' => 'El email interno ya está registrado.',
            'email_interno.required' => 'El email interno es obligatorio.',

            
            'email_externo.email' => 'El email externo no tiene un formato válido.',
            'email_externo.unique' => 'El email externo ya está registrado.',

            
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'fecha_nacimiento.before_or_equal' => 'Debes ser mayor de 18 años.',

            'admin.boolean' => 'El campo administrador es inválido.',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'name',
            'nombre_interno' => 'nombre interno',
            'password' => 'contraseña',
            'tel_interno' => 'tel_interno',
            'telefono_personal' => 'teléfono laboral',
            'email_interno' => 'email interno',
            'email_externo' => 'email externo',
            'fecha_nacimiento' => 'fecha de nacimiento',
        ];
    }
}
