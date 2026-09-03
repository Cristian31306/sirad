<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRadicadoRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_radicado' => 'required|string|max:255|unique:radicados,numero_radicado',
            'fecha_radicacion' => 'required|date|before_or_equal:today',
            'remitente' => 'required|string|max:255',
            'empresa' => 'nullable|string|max:255',
            'tipo_tramite_id' => 'required|exists:tipo_tramites,id',
            'asunto' => 'required|string',
            'medio' => 'required|string|max:255',
            'prioridad' => 'required|in:Alta,Media,Baja',
            'observaciones' => 'nullable|string',
            'responsables' => 'required|array|min:1',
            'responsables.*' => 'exists:responsables,id',
            'archivos_entrada' => 'nullable|array|max:20',
            'archivos_entrada.*' => 'file|max:25600|mimes:pdf,doc,docx,xls,xlsx,zip,rar,7z,jpg,jpeg,png',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numero_radicado.required' => 'El número de radicado es obligatorio.',
            'numero_radicado.unique' => 'Este número de radicado ya ha sido registrado previamente.',
            'fecha_radicacion.required' => 'La fecha de radicación es obligatoria.',
            'fecha_radicacion.before_or_equal' => 'La fecha de radicación no puede ser una fecha futura.',
            'remitente.required' => 'El nombre del remitente es obligatorio.',
            'tipo_tramite_id.required' => 'Debe seleccionar un tipo de trámite.',
            'tipo_tramite_id.exists' => 'El tipo de trámite seleccionado no es válido.',
            'asunto.required' => 'El asunto del radicado es obligatorio.',
            'medio.required' => 'Debe seleccionar el medio de recepción.',
            'prioridad.required' => 'Debe seleccionar la prioridad del trámite.',
            'responsables.required' => 'Debe seleccionar al menos un funcionario responsable.',
            'responsables.min' => 'Debe seleccionar al menos un funcionario responsable.',
            'archivos_entrada.max' => 'No puedes subir más de 20 archivos al mismo tiempo.',
            'archivos_entrada.*.max' => 'Cada archivo no puede superar los 25 MB.',
            'archivos_entrada.*.mimes' => 'Solo se permiten archivos en formato PDF, Word, Excel, Imágenes (JPG, PNG) o Comprimidos (ZIP, RAR, 7Z).',
        ];
    }
}
