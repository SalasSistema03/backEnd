<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecordatorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cambiá esto si necesitás restricciones de acceso
    }

    public function rules(): array
    {
        return [
            'descripcion' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'hora' => 'required',
            'intervalo' => 'required|in:Diario,Mensual',
            'cantidad' => 'required|integer|min:1',
            'repetir' => 'required|integer|min:1',
            'agenda_id' => 'required|exists:agendas,id',
            'usuario_id' => 'required|exists:users,id',
            'usuario_finaliza' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto.',
            'descripcion.max' => 'La descripción no debe superar los 255 caracteres.',

            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',

            'hora.required' => 'La hora es obligatoria.',

            'intervalo.required' => 'El intervalo es obligatorio.',
            'intervalo.in' => 'El intervalo debe ser Diario o Mensual.',

            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',

            'repetir.required' => 'El campo repetir es obligatorio.',
            'repetir.integer' => 'El campo repetir debe ser un número entero.',
            'repetir.min' => 'El campo repetir debe ser al menos 1.',

            'agenda_id.required' => 'Debe seleccionar una agenda.',
            'agenda_id.exists' => 'La agenda seleccionada no existe.',

            'usuario_id.required' => 'Debe indicar el usuario que carga el recordatorio.',
            'usuario_id.exists' => 'El usuario que carga no existe en el sistema.',

            'usuario_finaliza.exists' => 'El usuario que finaliza no existe en el sistema.',
        ];
    }
}
