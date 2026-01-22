<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FiltrarPropiedadRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta solicitud.
     * 
     * En este caso siempre se permite el acceso, ya que la verificacion de permisos
     * se realiza en el controlador mediante servicios
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Define las reglas de validación para los filtros de propiedades.
     *
     * Aquí se valida:
     *  - Búsqueda general por texto.
     *  - Listas de filtros como tipo de inmueble y zona.
     *  - Importes con rangos válidos.
     *  - Opciones booleanas/numéricas como cochera y oferta.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Texto libre para búsqueda. Máximo 255 caracteres.
            'search_term' => 'nullable|string|max:255|min:0',

            // Lista de tipos de inmueble. Debe ser array.
            'tipo_inmueble' => 'nullable|array',

            // Cada elemento debe existir en la tabla tipo_inmueble.
            'tipo_inmueble.*' => 'integer|exists:tipo_inmueble,id',

            // Lista de zonas.
            'zona' => 'nullable|array',

            // Cada zona debe existir en la tabla zona.
            'zona.*' => 'integer|exists:zona,id',

            // Estado de cochera: 0 = no, 1 = sí, 2 = indistinto.
            'cochera' => 'nullable|in:0,1,2',

            // Rango de importes para filtrar propiedades.
            'importe_minimo' => 'nullable|numeric|min:0',
            'importe_maximo' => 'nullable|numeric|min:0',

            // Indica si la propiedad está en oferta.
            'oferta' => 'nullable',

            // Cantidad mínima de dormitorios.
            'cantidad_dormitorios' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Mensajes personalizados para errores de validación.
     *
     * Estos mensajes reemplazan los predeterminados, permitiendo indicar
     * descripciones más claras y adaptadas al usuario final.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'importe_minimo.min' => 'El importe mínimo no puede ser negativo.',
            'importe_maximo.min' => 'El importe máximo no puede ser negativo.',
            'moneda.in' => 'La moneda debe ser "$" o "u$s".',
            'oferta.in' => 'Completar',
            'tipo_inmueble.exists' => 'El tipo de inmueble seleccionado no es válido.',
            'zona.exists' => 'La zona seleccionada no es válida.',
            'cantidad_dormitorios.min' => 'La cantidad de dormitorios debe ser mayor o igual a 0.',
        ];
    }
}
