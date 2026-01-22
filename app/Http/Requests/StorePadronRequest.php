<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StorePadronRequest
 *
 * Esta clase define las reglas de validación y los mensajes de error personalizados 
 * para el formulario relacionado con la creación o actualización del recurso Padron.
 * 
 * @package App\Http\Requests
 */
class StorePadronRequest extends FormRequest
{
    /**
     * Determine si el usuario está autorizado para realizar esta solicitud.
     *
     * @return bool Devuelve `true` si el usuario está autorizado, en este caso siempre es verdadero.
     * 
     * @note Este método se puede personalizar para implementar lógica de autorización basada en permisos o roles.
     */
    public function authorize(): bool
    {
        return true; // Permitir la solicitud de forma predeterminada.
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     *         Un arreglo asociativo donde cada clave representa un campo del formulario y
     *         el valor define las reglas de validación aplicables a dicho campo.
     * 
     * @note Las reglas garantizan que los datos ingresados cumplan con los formatos y restricciones esperados.
     */
    public function rules(): array
    {
        return [
            'nombre'       => 'required|string', // Campo obligatorio, cadena de texto, máximo 100 caracteres.
            'apellido'        => 'required|string', // Campo obligatorio, cadena de texto, máximo 100 caracteres.
            'fecha_nacimiento'         => 'nullable|date',           // Campo opcional, debe ser una fecha válida.
            'calle'           => 'nullable|string', // Campo opcional, cadena de texto, máximo 100 caracteres.
            'numero_calle'    => 'nullable|integer',        // Campo opcional, debe ser un número entero.
            'piso_departamento'  => 'nullable|string', // Campo opcional, cadena de texto, máximo 100 caracteres.
            'ciudad'             => 'nullable|string', // Campo opcional, cadena de texto, máximo 100 caracteres.
            'provincia'            => 'nullable|string', // Campo opcional, cadena de texto, máximo 100 caracteres.
            'notes'            => 'nullable|string',         // Campo opcional, sin límite específico de longitud.
        ];
    }

    /**
     * Obtener los mensajes de error personalizados para las reglas de validación.
     *
     * @return array<string, string> Un arreglo asociativo donde las claves son las reglas 
     *                               y los valores son los mensajes de error personalizados.
     * 
     * @note Es útil para mostrar mensajes específicos en el idioma deseado, mejorando la experiencia del usuario.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Por favor ingrese un nombre.',   // Mensaje de error para el campo 'first_name'.
            'apellido.required'  => 'Por favor ingrese un apellido.', // Mensaje de error para el campo 'last_name'.
        ];
    }
}
