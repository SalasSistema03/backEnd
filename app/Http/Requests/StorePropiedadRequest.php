<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropiedadRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Cambiar según la lógica de autenticación
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {

        return [
            // Ubicación

            'calle' => 'required|integer|exists:calle,id',
            /* 'numero_calle' => 'nullable|integer|required_with:numero_calle|min:0|max:100000', */
            'piso' => 'nullable|string|min:0|max:99',
            'depto' => 'nullable|string|min:0|max:600', 
            'zona' => 'required|integer|exists:zona,id',
            /* 'barrio' => 'nullable|integer|exists:barrio,id',
            'localidad' => 'nullable|integer|exists:localidad,id', */
            'provincia' => 'required|integer|exists:provincia,id',
            'folio' => 'nullable|integer|min:0',

            // Características generales
            'tipo_inmueble' => 'nullable|integer|exists:tipo_inmueble,id', //O EN SU DEFECTO ID_INMUEBLE
            'estado_general' => 'nullable|integer|exists:estado_general,id',
            'dormitorios' => 'nullable|integer|min:0|max:100',
            'banios' => 'nullable|integer|min:0|max:100',
            'cochera' => 'nullable|in:SI,NO',
            'asfalto' => 'nullable|in:SI,NO',
            'cloaca' => 'nullable|in:SI,NO',
            'gas' => 'nullable|in:SI,NO',
            'agua' => 'nullable|in:SI,NO',
           /*  'numero_cochera' => 'nullable|string|min:0|max:10|nullable_if:cochera,1', */
            /* 'm_Lote' => 'nullable|numeric|min:0',
            'm_Cubiertos' => 'nullable|numeric|min:0|lte:m_Lote', */

            // Venta y alquiler|unique:propiedades,cod_venta,|unique:propiedades,cod_alquiler
            'cod_venta' => 'nullable|integer|min:0|max:100000|required_without:cod_alquiler',
            'cod_alquiler' => 'nullable|integer|min:0|max:100000|required_without:cod_venta',
          
            'estado_venta' => 'nullable|integer|exists:estado_ventas,id',
            'comparte_venta' => 'nullable|in:SI,NO',
            'autorizacion_venta' => 'nullable|in:SI,NO',
            'fecha_autorizacion_venta' => 'nullable|date',
            'exclusividad_venta' => 'nullable|in:SI,NO',
            'condicionado_venta' => 'nullable|in:SI,NO',
           
            'estado_alquiler' => 'nullable|integer|exists:estado_alquileres,id',
            'autorizacion_alquiler' => 'nullable|in:SI,NO',
            'fecha_autorizacion_alquiler' => 'nullable|date',
            'exclusividad_alquiler' => 'nullable|in:SI,NO',
            'clausula_de_venta' => 'nullable|in:SI,NO',
            'tiempo_clausula' => 'nullable|integer|min:0|max:100000',
            'venta_fecha_alta'=>'nullable|date',
            'alquiler_fecha_alta' =>'nullable|date',
            'fecha_publicacion_ig' => 'nullable|date',

            // Información económica y tasación
            'tasacion_venta' => 'nullable|integer|min:0',
            'moneda_venta' => 'nullable|in:1,2',
            'fecha_tasacion_venta' => 'nullable|date',
            'monto_venta' => 'nullable|min:0',
            'moneda_alquiler' => 'nullable|in:1,2',
            'monto_alquiler' => 'nullable|min:0',

            // Estado y otros detalles
            /* 'llave' => 'nullable|integer|nullable_with:llave|min:0|max:100', */
            'observacion_llave' => 'nullable|string|max:255',
            'cartel' => 'required|in:SI,NO',
            'observacion_cartel' => 'nullable|string|min:0|max:255', 



        ];
    }

    
    public function messages(): array
    {
        return [
            // Ubicación
            'calle.integer' => 'Calle no encontrada',
            'calle.exists' => 'La calle seleccionada no existe en nuestra base de datos',
            'calle.required' => 'La calle es obligatoria',
            
            'piso.string' => 'El campo piso debe ser texto',
            'piso.min' => 'El campo piso no puede ser menor a 0',
            'piso.max' => 'El campo piso no puede exceder los 99 caracteres',
            
            'depto.string' => 'El campo departamento debe ser texto',
            'depto.min' => 'El campo departamento no puede ser menor a 0',
            'depto.max' => 'El campo departamento no puede exceder los 600 caracteres',
            
            'zona.integer' => 'Zona no encontrada',
            'zona.exists' => 'La zona seleccionada no existe en nuestra base de datos',
            'zona.required' => 'La zona es obligatoria',
            
            'provincia.required' => 'El campo provincia es obligatorio',
            'provincia.integer' => 'El campo provincia debe ser un número entero',
            'provincia.exists' => 'La provincia seleccionada no existe en nuestra base de datos',
            
            'folio.integer' => 'El campo folio debe ser un número entero',
            'folio.min' => 'El campo folio no puede ser menor a 0',

            // Características generales
            'tipo_inmueble.integer' => 'Tipo de inmueble no encontrado',
            'tipo_inmueble.exists' => 'El tipo de inmueble seleccionado no existe en nuestra base de datos',
            
            'estado_general.integer' => 'Estado general no encontrado',
            'estado_general.exists' => 'El estado general seleccionado no existe en nuestra base de datos',
            
            'dormitorios.integer' => 'El campo dormitorios debe ser un número entero',
            'dormitorios.min' => 'El campo dormitorios no puede ser menor a 0',
            'dormitorios.max' => 'El campo dormitorios no puede exceder 100',
            
            'banios.integer' => 'El campo baños debe ser un número entero',
            'banios.min' => 'El campo baños no puede ser menor a 0',
            'banios.max' => 'El campo baños no puede exceder 100',
            
            'cochera.in' => 'El campo cochera debe ser SI o NO',
            'asfalto.in' => 'El campo asfalto debe ser SI o NO',
            'cloaca.in' => 'El campo cloaca debe ser SI o NO',
            'gas.in' => 'El campo gas debe ser SI o NO',
            'agua.in' => 'El campo agua debe ser SI o NO',

            // Venta y alquiler
            'cod_venta.integer' => 'El código de venta debe ser un número entero',
            'cod_venta.min' => 'El código de venta no puede ser menor a 0',
            'cod_venta.max' => 'El código de venta no puede exceder 100000',
            'cod_venta.required_without' => 'Debe ingresar al menos un código de venta o alquiler',
            
            'cod_alquiler.integer' => 'El código de alquiler debe ser un número entero',
            'cod_alquiler.min' => 'El código de alquiler no puede ser menor a 0',
            'cod_alquiler.max' => 'El código de alquiler no puede exceder 100000',
            'cod_alquiler.required_without' => 'Debe ingresar al menos un código de venta o alquiler',
            
            'estado_venta.integer' => 'Estado de venta no encontrado',
            'estado_venta.exists' => 'El estado de venta seleccionado no existe en nuestra base de datos',
            
            'comparte_venta.in' => 'El campo comparte venta debe ser SI o NO',
            'autorizacion_venta.in' => 'El campo autorización de venta debe ser SI o NO',
            'fecha_autorizacion_venta.date' => 'La fecha de autorización de venta debe ser una fecha válida',
            'exclusividad_venta.in' => 'El campo exclusividad de venta debe ser SI o NO',
            'condicionado_venta.in' => 'El campo condicionado de venta debe ser SI o NO',
            
            'estado_alquiler.integer' => 'El campo estado de alquiler debe ser un número entero',
            'estado_alquiler.exists' => 'El estado de alquiler seleccionado no existe en nuestra base de datos',
            
            'autorizacion_alquiler.in' => 'El campo autorización de alquiler debe ser SI o NO',
            'fecha_autorizacion_alquiler.date' => 'La fecha de autorización de alquiler debe ser una fecha válida',
            'exclusividad_alquiler.in' => 'El campo exclusividad de alquiler debe ser SI o NO',
            'clausula_de_venta.in' => 'El campo cláusula de venta debe ser SI o NO',
            
            'tiempo_clausula.integer' => 'El campo tiempo de cláusula debe ser un número entero',
            'tiempo_clausula.min' => 'El campo tiempo de cláusula no puede ser menor a 0',
            'tiempo_clausula.max' => 'El campo tiempo de cláusula no puede exceder 100000',

            // Información económica y tasación
            'tasacion_venta.integer' => 'El campo tasación de venta debe ser un número entero',
            'tasacion_venta.min' => 'El campo tasación de venta no puede ser menor a 0',
            
            'moneda_venta.in' => 'El campo moneda de venta debe ser 1 (pesos) o 2 (dólares)',
            'fecha_tasacion_venta.date' => 'La fecha de tasación de venta debe ser una fecha válida',
            
            'monto_venta.numeric' => 'El campo precio de venta debe ser un número',
            'monto_venta.min' => 'El campo precio de venta no puede ser menor a 0',
            
            'moneda_alquiler.in' => 'El campo moneda de alquiler debe ser 1 (pesos) o 2 (dólares)',
            
            'monto_alquiler.integer' => 'El campo precio de alquiler debe ser un número entero',
            'monto_alquiler.min' => 'El campo monto de alquiler no puede ser menor a 0',

            // Estado y otros detalles
            'observacion_llave.string' => 'El campo observación de llave debe ser texto',
            'observacion_llave.max' => 'El campo observación de llave no puede exceder los 255 caracteres',
            
            'cartel.required' => 'El campo cartel es obligatorio',
            'cartel.in' => 'El campo cartel debe ser SI o NO',
            
            'observacion_cartel.string' => 'El campo observación de cartel debe ser texto',
            'observacion_cartel.min' => 'El campo observación de cartel no puede ser menor a 0',
            'observacion_cartel.max' => 'El campo observación de cartel no puede exceder los 255 caracteres',
        ];
    }
}
